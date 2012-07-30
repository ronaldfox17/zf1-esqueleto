<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

/******************************************************************************/

    protected function _initNavigation()
    {
        $config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/navigation.xml');

        $view = $this->bootstrap('view')->getResource('view');

        $navigation = $view->navigation();
        $navigation->addPages($config);
    }

/******************************************************************************/

    protected function _initLogging()
    {
        $this->bootstrap('frontController');
        $logger = new Zend_Log();

        $environment = $this->getEnvironment();

        if ($environment == 'production') {
            // Log em arquivo
            $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH.'/log/application.log');
            
        }else{
            // Log pelo firebug
            $writer = new Zend_Log_Writer_Firebug();
        }

        $logger->addWriter($writer);
        $logger->setTimestampFormat('d-m-Y H:i:s');
        //$logger->log('teste', Zend_Log::INFO);

        Zend_Registry::set('logger', $logger);
    }

/******************************************************************************/

    protected function _initDb()
    {
        $dbConfig = new Zend_Config_Ini(
            APPLICATION_PATH . '/configs/application.ini',
            APPLICATION_ENV
        );

        $db = Zend_Db::factory(
            $dbConfig->resources->db->adapter,
            $dbConfig->resources->db->params->toArray()
        );

        Zend_Db_Table_Abstract::setDefaultAdapter($db);

        Zend_Registry::set('db', $db);
    }

/******************************************************************************/

    protected function _initDbProfiler()
    {
        $this->bootstrap('db');

        $profiler = new Zend_Db_Profiler_Firebug('DB Queries');
        $profiler->setEnabled(true);

        Zend_Registry::get('db')->setProfiler($profiler);
    }

/******************************************************************************/

    protected function _initAutoload()
    {
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->registerNamespace('Image');
        $loader->setFallbackAutoloader(true);
    }

/******************************************************************************/

    protected function _initCharset()
    {
        header('Content-type: text/html; charset=UTF-8');
    }

/******************************************************************************/

    protected function _initSession()
    {
        Zend_Session::start();
        Zend_Registry::set('session', new Zend_Session_Namespace());
    }

/******************************************************************************/

    protected function _initLocale()
    {
        $locale = new Zend_Locale('pt_BR');
        Zend_Registry::set('locale', $locale);
    }

/******************************************************************************/

    protected function _initTimeZone()
    {
        date_default_timezone_set('America/Sao_Paulo');        
    }

/******************************************************************************/

    protected function _initDate()
    {
        // Formato de data para o mysql
        if (!defined('DATE_FORMAT_DB')) {
            define('DATE_FORMAT_DB', 'yyyy-MM-dd HH:mm:ss');
        }

        // Formato de data para exibir na aplicação
        if (!defined('DATE_FORMAT_APPLICATION')) {
            define('DATE_FORMAT_APPLICATION', 'dd-MM-yyyy HH:mm:ss');
        }
    }

/******************************************************************************/

    protected function _initCache()
    {
        $frontendOptions = array(
            'automatic_serialization' => true,
            'lifetime'                => 7200
        );

        $backendOptions = array(
            'cache_dir' => APPLICATION_PATH.'/cache'
        );

        $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);

        // Salva o cache no Registry
        Zend_Registry::set('cache', $cache);

        // Cache de metadados das tabelas
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
    }

/******************************************************************************/

    protected function _initHelperPath()
    {
        Zend_Controller_Action_HelperBroker::addPath( APPLICATION_PATH . '/controllers/helpers' );
    }

/******************************************************************************/

    protected function _initRoutes()
    {
        return null;
    }

/******************************************************************************/

    protected function _initZFDebug()
    {
        if ('development' == $this->getEnvironment() && ZF_DEBUG === true) {
            $autoloader = Zend_Loader_Autoloader::getInstance();
            $autoloader->registerNamespace('ZFDebug');

            $options = array(
                'plugins' => array(
                    'Variables',
                    'Database' => array(
                        'adapter' => Zend_Registry::get('db')
                     ),
                    'File' => array(
                        'basePath' => APPLICATION_PATH . "../library/ZFDebug/"
                    ),
                    'Cache' => array('backend' => ''),
                    'Exception')
            );
            $debug = new ZFDebug_Controller_Plugin_Debug($options);

            $this->bootstrap('frontController');
            $frontController = $this->getResource('frontController');
            $frontController->registerPlugin($debug);
        }
    }

/******************************************************************************/

}
