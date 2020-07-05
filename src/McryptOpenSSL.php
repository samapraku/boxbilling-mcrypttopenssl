<?php
/**
 * BoxBilling Mcrypt to OpenSSL conversion script.
 *
 * @copyright BoxBilling, Inc (http://www.boxbilling.com)
 * @license   Apache-2.0
 */

class McryptOpenSSL
{
    protected $di;

    private $openssl_crypt;

    public function __construct($di){
        $this->setDi($di);
        $this->openssl_crypt = new CryptOpenSSL($di);
    }

    /**
     * @param mixed $di
     */
    public function setDi($di)
    {
        $this->di = $di;
    }

    /**
     * @return mixed
     */
    public function getDi()
    {
        return $this->di;
    }

    // convert email template variables from mcrypt to openssl
    public function convert_email_template_to_openssl(){       
        $db = $this->di['db'];
        $templates  = $db->find('EmailTemplate', 'vars <> \'\'');
        $emailService = $this->di['mod_service']('email');
        foreach($templates as $template){
            $vars = $emailService->getVars($template);
            $this->setVars($template, $vars);
        }

        return true;      
    }

    private function setVars($t, $vars)
    {
        $t->vars = $this->openssl_crypt->encrypt(json_encode($vars), 'v8JoWZph12DYSY4aq8zpvWdzC');
        $this->di['db']->store($t); 
        return true;
    }

    // convert extension configurations from mcrypt to openssl
    public function extension_config_to_openssl(){       
        $db = $this->di['db'];
        $configs = $this->di['db']->find('ExtensionMeta', 'meta_key = :key AND (meta_value IS NOT NULL AND meta_value <> \'\')', array(':key'=>'config'));
        $extService = $this->di['mod_service']('extension');
        foreach($configs as $config){
            $data = $extService->getConfig($config->extension);           
            $this->setConfig($data);
        }
        return true;      
    }

    private function setConfig($data)
    {
        $this->di['events_manager']->fire(array('event'=>'onBeforeAdminExtensionConfigSave', 'params'=>$data));
        $sql="
            UPDATE extension_meta
            SET meta_value = :config
            WHERE extension = :ext
            AND meta_key = 'config'
            LIMIT 1;
        ";

        $config = json_encode($data);
        $config = $this->openssl_crypt->encrypt($config);

        $params = array(
            'ext'        => $data['ext'],
            'config'     => $config,
        );
        $this->di['db']->exec($sql, $params);
        $this->di['events_manager']->fire(array('event'=>'onAfterAdminExtensionConfigSave', 'params'=>$data));
        $this->di['logger']->info('Updated extension "%s" configuration', $data['ext']);

        return true;
    }

}