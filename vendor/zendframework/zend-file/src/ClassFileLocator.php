<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\File;

use DirectoryIterator;
use FilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Locate files containing PHP classes, interfaces, abstracts or traits
 */
class ClassFileLocator extends FilterIterator
{
    /**
     * Create an instance of the locator iterator
     *
     * Expects either a directory, or a DirectoryIterator (or its recursive variant)
     * instance.
     *
     * @param  string|DirectoryIterator $dirOrIterator
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($dirOrIterator = '.')
    {
        if (is_string($dirOrIterator)) {
            if (! is_dir($dirOrIterator)) {
                throw new Exception\InvalidArgumentException('Expected a valid directory name');
            }

            $dirOrIterator = new RecursiveDirectoryIterator(
                $dirOrIterator,
                RecursiveDirectoryIterator::FOLLOW_SYMLINKS
            );
        } elseif (! $dirOrIterator instanceof DirectoryIterator) {
            throw new Exception\InvalidArgumentException('Expected a DirectoryIterator');
        }

        if ($dirOrIterator instanceof RecursiveIterator) {
            $dirOrIterator = new RecursiveIteratorIterator($dirOrIterator);
        }

        parent::__construct($dirOrIterator);
        $this->setInfoClass(PhpClassFile::class);
    }

    /**
     * Filter for files containing PHP classes, interfaces, or abstracts
     *
     * @return bool
     */
    public function accept()
    {
        $file = $this->getInnerIterator()->current();
        // If we somehow have something other than an SplFileInfo object, just
        // return false
        if (! $file instanceof SplFileInfo) {
            return false;
        }

        // If we have a directory, it's not a file, so return false
        if (! $file->isFile()) {
            return false;
        }

        // If not a PHP file, skip
        if ($file->getBasename('.php') == $file->getBasename()) {
            return false;
        }

        $contents = file_get_contents($file->getRealPath());
        $tokens   = token_get_all($contents);
        $count    = count($tokens);
        $inFunctionDeclaration = false;
        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            // single character token found; skip
            if (! is_array($token)) {
                // If we were in a function declaration, and we encounter an
                // opening paren, reset the $inFunctionDeclaration flag.
                if ('(' === $token) {
                    $inFunctionDeclaration = false;
                }

                $i++;
                continue;
            }

            switch ($token[0]) {
                case T_NAMESPACE:
                    // Namespace found; grab it for later
                    $namespace = '';
                    for ($i++; $i < $count; $i++) {
                        $token = $tokens[$i];
                        if (is_string($token)) {
                            if (';' === $token) {
                                $saveNamespace = false;
                                break;
                            }
                            if ('{' === $token) {
                                $saveNamespace = true;
                                break;
                            }
                            continue;
                        }
                        list($type, $content, $line) = $token;
                        switch ($type) {
                            case T_STRING:
                            case T_NS_SEPARATOR:
                                $namespace .= $content;
                                break;
                        }
                    }
                    if ($saveNamespace) {
                        $savedNamespace = $namespace;
                    }
                    break;
                case T_FUNCTION:
                    // `use function` should not enter function context
                    if ($i < 2 || ! is_array($tokens[$i - 2]) || $tokens[$i - 2][0] !== T_USE) {
                        $inFunctionDeclaration = true;
                    }
                    break;
                case T_TRAIT:
                case T_CLASS:
                    // ignore T_CLASS after T_DOUBLE_COLON to allow PHP >=5.5 FQCN scalar resolution
                    if ($i > 0 && is_array($tokens[$i - 1]) && $tokens[$i - 1][0] === T_DOUBLE_COLON) {
                        break;
                    }

                    // Ignore if we are within a function declaration;
                    // functions are allowed to be named after keywords
                    // such as class, interface, and trait.
                    if ($inFunctionDeclaration) {
                        break;
                    }

                    // ignore anonymous classes on PHP 7.1 and greater
                    if ($i >= 2
                        && \is_array($tokens[$i - 1])
                        && T_WHITESPACE === $tokens[$i - 1][0]
                        && \is_array($tokens[$i - 2])
                        && T_NEW === $tokens[$i - 2][0]
                    ) {
                        break;
                    }

                    // no break
                case T_INTERFACE:
                    // Abstract class, class, interface or trait found

                    // Ignore if we are within a function declaration;
                    // functions are allowed to be named after keywords
                    // such as class, interface, and trait.
                    if ($inFunctionDeclaration) {
                        break;
                    }

                    // Get the classname
                    for ($i++; $i < $count; $i++) {
                        $token = $tokens[$i];
                        if (is_string($token)) {
                            continue;
                        }
                        list($type, $content, $line) = $token;
                        if (T_STRING == $type) {
                            // If a classname was found, set it in the object, and
                            // return boolean true (found)
                            if (! isset($namespace) || null === $namespace) {
                                if (isset($saveNamespace) && $saveNamespace) {
                                    $namespace = $savedNamespace;
                                } else {
                                    $namespace = null;
                                }
                            }
                            $class = (null === $namespace) ? $content : $namespace . '\\' . $content;
                            $file->addClass($class);
                            if ($namespace) {
                                $file->addNamespace($namespace);
                            }
                            $namespace = null;
                            break;
                        }
                    }
                    break;
                default:
                    break;
            }
        }
        $classes = $file->getClasses();
        if (! empty($classes)) {
            return true;
        }
        // No class-type tokens found; return false
        return false;
    }
}
