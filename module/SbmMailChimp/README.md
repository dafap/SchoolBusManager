#Using the `DreaM\MailChimp` library in a project Zend Framework 2

##To install the library :
    composer require drewm/mailchimp-api

##To use the library in a project Zend Framework 2
You must specify its path in the method `Module::getAutoloaderConfig()` of the modules that will use it :

    public function getAutoloaderConfig()
    {
        return array(
                'Zend\Loader\ClassMapAutoloader' => array(
                        __DIR__ . '/autoload_classmap.php',
                ),
                'Zend\Loader\StandardAutoloader' => array(
                        'namespaces' => array(
                                __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                        ),
                ),
        );
    }

Note that there is no file `autoload_classmap.php` in the library `DrewM\MailChimp`.

If the project uses the `ZfcBase` Module, the `Module :: getAutoloaderConfig ()` method becomes :

    public function getAutoloaderConfig()
    {
        $autoload = array_merge_recursive(parent::getAutoloaderConfig(), [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    'DrewM\MailChimp' => realpath(__DIR__ . '/../../vendor/drewm/mailchimp-api/src')
                ]
            ]
        ]);
        return $autoload;
    }

##To use the library and `Zend\Paginator`
You must write a class `Paginator\Adapter\MailChimpAdapter`.

    namespace MyProject\Model\Paginator\Adapter
    use Zend\Paginator\Adapter\AdapterInterface;
    use DrewM\MailChimp\MailChimp;
    use SbmCommun\Model\StdLib;
    class MailChimpAdapter implements AdapterInterface
    {
    
        /**
         *
         * @var MailChimp
         */
        protected $mailchimp;
    
        /**
         * 
         * @var string
         */
        protected $method;
    
        /**
         * container name (lists, segments, members, merge_fields ...)
         * 
         * @var string
         */
        protected $container;
        
        /**
         * 
         * @var int
         */
        protected $count;

        public function __construct(MailChimp $mailchimp, $method, $container)
        {
            $this->mailchimp = $mailchimp;
            $this->method = $method;
            $this->container = $container;
        }

        /**
         * (non-PHPdoc)
         * @see \Zend\Paginator\Adapter\AdapterInterface::getItems()
         */
        public function getItems($offset, $itemCountPerPage)
        {
            $method = sprintf('%s?offset=%d&count=%d', $this->method, $offset, $itemCountPerPage);
            return $this->mailchimp->get($method)[$this->container];
    }

        /**
         * We send the `get` request without specifying `offset` and `limit` because by default, offset = 0 and limit = 10.
         * In the result, the `total items` key contains the total number of items matching the query regardless of pagination.
         * (non-PHPdoc)
         * @see Countable::count()
         */
        public function count()
        {
            if ($this->count === null) {
                $this->count = StdLib::getParam('total_items', $this->mailchimp->get($this->method));
            }
            return $this->count;
        }
    }

##Using in controller (or elsewhere) :

    use Zend\Paginator\Paginator;
    use MyProject\Model\Paginator\Adapter\MailChimpAdapter;
    
    // reading lists
    $method = 'lists';
    $lists = new Paginator(new MailChimpAdapter($mailchimp, $method, 'lists'));
    
    // reading segments
    $method = 'lists/' . $args['id_liste'] . '/segments';
    $egments = new Paginator(new MailChimpAdapter($mailchimp, $method, 'segments'));
    
    // reading a segment members
    $method = 'lists/' . $args['id_liste'] . '/segments/' . $args['segment_id'] . '/members';
    $source = new Paginator(new MailChimpAdapter($mailchimp, $method, 'members'));
    
    // reading a list fields 
    // Note the difference between writing the operator in the method (merge-fields) and the container (merge_fields)
    $method = 'lists/' . $args['id_liste'] . '/merge-fields';
    $source = new Paginator(new MailChimpAdapter($mailchimp, $method, 'merge_fields'));
