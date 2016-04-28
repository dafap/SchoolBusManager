#Utilisation de la bibliothèque `DrewM\MailChimp` dans un projet ZendFramework2

##Pour installer la bibliothèque :
    composer require drewm/mailchimp-api

##Pour utiliser la bibliothèque dans un projet ZendFramework2 
Il faut indiquer son chemin dans la méthode `Module::getAutoloaderConfig()` des modules qui l’utiliseront :

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

À noter qu’il n’y a pas de fichier `autoload_classmap.php` dans la bibliothèque `DrewM\MailChimp`.

Si le projet utilise le module `ZfcBase`, la méthode `Module::getAutoloaderConfig()` devient :

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

##Pour utiliser la bibliothèque et les paginateurs de ZendFramework 
Il faut écrire un Paginator\Adapter\MailChimpAdapter.

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
         * Nom du container (lists, segments, members, merge_fields ...)
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
         * On lance la requête `get` sans préciser `offset` et `limit` car par défaut, offset = 0 et limit = 10.
         * Dans le résultat, la clé `total_items` contient l'effectif total.
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

##Utilisation dans un controlleur (ou ailleurs) :

    use Zend\Paginator\Paginator;
    use MyProject\Model\Paginator\Adapter\MailChimpAdapter;
    
    // lecture des listes
    $method = 'lists';
    $lists = new Paginator(new MailChimpAdapter($mailchimp, $method, 'lists'));
    
    // lecture des segments
    $method = 'lists/' . $args['id_liste'] . '/segments';
    $egments = new Paginator(new MailChimpAdapter($mailchimp, $method, 'segments'));
    
    // lecture des membres d’un segments
    $method = 'lists/' . $args['id_liste'] . '/segments/' . $args['segment_id'] . '/members';
    $source = new Paginator(new MailChimpAdapter($mailchimp, $method, 'members'));
    
    // lecture des champs d’une liste. 
    // Noter la différence d’écriture entre l’opérateur dans la méthode (merge-fields) et le container (merge_fields)
    $method = 'lists/' . $args['id_liste'] . '/merge-fields';
    $source = new Paginator(new MailChimpAdapter($mailchimp, $method, 'merge_fields'));
