<?php
/**
 * @see       https://github.com/zendframework/zend-feed for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-feed/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Feed\Writer\Extension\GooglePlayPodcast;

use PHPUnit\Framework\TestCase;
use Zend\Feed\Writer;
use Zend\Feed\Writer\Exception\ExceptionInterface;

class FeedTest extends TestCase
{
    public function testSetBlock()
    {
        $feed = new Writer\Feed;
        $feed->setPlayPodcastBlock('yes');
        $this->assertEquals('yes', $feed->getPlayPodcastBlock());
    }

    public function testSetBlockThrowsExceptionOnNonAlphaValue()
    {
        $this->expectException(ExceptionInterface::class);
        $feed = new Writer\Feed;
        $feed->setPlayPodcastBlock('123');
    }

    public function testSetBlockThrowsExceptionIfValueGreaterThan255CharsLength()
    {
        $this->expectException(ExceptionInterface::class);
        $feed = new Writer\Feed;
        $feed->setPlayPodcastBlock(str_repeat('a', 256));
    }

    public function testAddAuthors()
    {
        $feed = new Writer\Feed;
        $feed->addPlayPodcastAuthors(['joe', 'jane']);
        $this->assertEquals(['joe', 'jane'], $feed->getPlayPodcastAuthors());
    }

    public function testAddAuthor()
    {
        $feed = new Writer\Feed;
        $feed->addPlayPodcastAuthor('joe');
        $this->assertEquals(['joe'], $feed->getPlayPodcastAuthors());
    }

    public function testAddAuthorThrowsExceptionIfValueGreaterThan255CharsLength()
    {
        $this->expectException(ExceptionInterface::class);
        $feed = new Writer\Feed;
        $feed->addPlayPodcastAuthor(str_repeat('a', 256));
    }

    public function testSetCategories()
    {
        $feed = new Writer\Feed;
        $cats = [
            'cat1',
            'cat2' => ['cat2-1', 'cat2-a&b']
        ];
        $feed->setPlayPodcastCategories($cats);
        $this->assertEquals($cats, $feed->getPlayPodcastCategories());
    }

    public function testSetCategoriesThrowsExceptionIfAnyCatNameGreaterThan255CharsLength()
    {
        $this->expectException(ExceptionInterface::class);
        $feed = new Writer\Feed;
        $cats = [
            'cat1',
            'cat2' => ['cat2-1', str_repeat('a', 256)]
        ];
        $feed->setPlayPodcastCategories($cats);
        $this->assertEquals($cats, $feed->getPlayPodcastCategories());
    }

    public function testSetImageAsPngFile()
    {
        $feed = new Writer\Feed;
        $feed->setPlayPodcastImage('http://www.example.com/image.png');
        $this->assertEquals('http://www.example.com/image.png', $feed->getPlayPodcastImage());
    }

    public function testSetImageAsJpgFile()
    {
        $feed = new Writer\Feed;
        $feed->setPlayPodcastImage('http://www.example.com/image.jpg');
        $this->assertEquals('http://www.example.com/image.jpg', $feed->getPlayPodcastImage());
    }

    public function testSetImageThrowsExceptionOnInvalidUri()
    {
        $this->expectException(ExceptionInterface::class);
        $feed = new Writer\Feed;
        $feed->setPlayPodcastImage('http://');
    }

    public function testSetExplicitToYes()
    {
        $feed = new Writer\Feed;
        $feed->setPlayPodcastExplicit('yes');
        $this->assertEquals('yes', $feed->getPlayPodcastExplicit());
    }

    public function testSetExplicitToNo()
    {
        $feed = new Writer\Feed;
        $feed->setPlayPodcastExplicit('no');
        $this->assertEquals('no', $feed->getPlayPodcastExplicit());
    }

    public function testSetExplicitToClean()
    {
        $feed = new Writer\Feed;
        $feed->setPlayPodcastExplicit('clean');
        $this->assertEquals('clean', $feed->getPlayPodcastExplicit());
    }

    public function testSetExplicitThrowsExceptionOnUnknownTerm()
    {
        $this->expectException(ExceptionInterface::class);
        $feed = new Writer\Feed;
        $feed->setPlayPodcastExplicit('abc');
    }

    public function testSetDescription()
    {
        $feed = new Writer\Feed;
        $feed->setPlayPodcastDescription('abc');
        $this->assertEquals('abc', $feed->getPlayPodcastDescription());
    }

    public function testSetDescriptionThrowsExceptionWhenValueExceeds4000Chars()
    {
        $this->expectException(ExceptionInterface::class);
        $feed = new Writer\Feed;
        $feed->setPlayPodcastDescription(str_repeat('a', 4001));
    }

    public function invalidImageUrls()
    {
        return [
            'null'                  => [null],
            'true'                  => [true],
            'false'                 => [false],
            'zero'                  => [0],
            'int'                   => [1],
            'zero-float'            => [0.0],
            'float'                 => [1.1],
            'string'                => ['scheme:/host.path'],
            'array'                 => [['https://example.com/image.png']],
            'object'                => [(object) ['image' => 'https://example.com/image.png']],
        ];
    }

    /**
     * @dataProvider invalidImageUrls
     * @param mixed $url
     */
    public function testSetPlayPodcastImageRaisesExceptionForInvalidUrl($url)
    {
        $feed = new Writer\Feed();
        $this->expectException(ExceptionInterface::class);
        $feed->setPlayPodcastImage($url);
    }

    public function validImageUrls()
    {
        return [
            'jpg' => ['https://example.com/image.jpg'],
            'png' => ['https://example.com/image.png'],
        ];
    }

    /**
     * @dataProvider validImageUrls
     * @param string $url
     */
    public function testSetPlayPodcastImageSetsInternalDataWithValidUrl($url)
    {
        $feed = new Writer\Feed();
        $feed->setPlayPodcastImage($url);
        $this->assertEquals($url, $feed->getPlayPodcastImage());
    }
}
