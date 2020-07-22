
# Boxbilling Mrypt-Openssl Migration tool
A migration script for migrating existing boxbilling installation from mcrypt to openssl for PHP 7.2 compatibility

## Boxbilling - PHP 7.2 compatible:
[https://github.com/samapraku/boxbilling](https://github.com/samapraku/boxbilling)

## Mcrypt Usage in Boxbilling
The major issue that makes upgrading boxbilling to PHP 7.x compatible is the use of Mcrypt which was dropped in PHP 7.2.   
Boxbilling employs encryption for:  
-Email templates variables and   
-Extension configurations.  

A careful consideration will show that a migration tool isn't necessary to upgrade an existing installation of boxbilling from mcrypt to openssl. You can simply upgrade your boxbilling installation and manually upgrade the database to work with the new one.

**Manually Updating Email Template Variables**  
Email Template variables are overwritten each time an event or action calls the hook that fires sending of the email template so any previously encrypted variables will eventually be overwritten with new values that are encrypted with Openssl.

**Manually Updating Extensions Configurations**  
Extension configurations are stored in encrypted format. Without a migration script, it is only a matter of going through the enabled boxbilling extensions and re-configurinng them. Saving the new values will overwrite the previous values. The cost involved here is the time it will take to re-configure the extensions. Given that boxbilling doesn't have a lot of extensions (and even only a few of the included extensions are enabled), it doesn't take much time.

## What this script does
That being said, this migration tool allows to keep existing extensions configurations by decrypting and encrypting them with OpenSSL. In addition, boxbilling's email templates use twig's 'filter' tags which has been deprecated in twig v2.9. This migration script searches and replaces the 'filter' tag with the new 'apply' tag in the email templates.
This script doesn't modify any installation files. 

## Steps to migrate from Mcrypt to OpenSSL
The order of the steps is important to achieve the desired results. 
- This script requires mcrypt in order to decrypt existing values. Since mcrypt is not available on PHP 7.2, this script has to be executed on a PHP 5.6 installation of boxbilling which I presume will be the case if you are not using the new boxbilling installation which is PHP 7.2 compatible.
- After executing the script to convert from mcrypt to openssl the PHP version can be updated to PHP 7.2 and the boxbilling installation files can be updated as well.

## How to migrate the database
To migrate an existing boxbilling installation, it is recommended to create backup of your database or test this tool on a non-production setup. 

This script modifies **email_template and extension_meta database tables**.
1. Place the content of src in a directory in the root of your existing boxbilling installation that still uses mcrypt. 
For example  
    `<boxbilling-dir>/<migrate-dir>/[content of src folder]`  

2. Run the script in your browser by opening:  
`http(s)://your-boxbilling.com/<migrate-dir>/migrate.php`

3. Delete the `<migrate-dir>` as it is no longer needed.  
4. Upgrade your server's PHP version to PHP 7.2
4. Update boxbilling installation to use the PHP 7.2 compatible files. 

### Updating Boxbilling
If you are using the default boxbilling installation without any custom modifications, then simply replace all the directories and files **except bb-config.php** file. 

The `bb-vendor` directory has to be updated. Composer has to be executed to install the updated dependencies. Composer will install dependencies based on composer.json file.  

Execute in your boxbilling directory:  
`composer install`  
If composer install fails, execute instead:  
`composer update`

If you are using a custom theme, you will need to update the theme's .phtml files to make it compatible with twig v2.9. You can use an IDE with find and replace capability to change old twig features in the theme files.  

Some of the things to change in the theme files will include:  
- "sameas" to "same as"
- "autoescape true js" to "autoescape "js""
- "{%raw%} {{price}} {%/endraw%}" to " price|raw "
- "{% filter markdown %}" to "{% apply markdown %}" 

When you experience a fatal error, set debug to true in bb-config.php to provide details about the error.

## Disclaimer
This script is provided as is without any guarantees. You use this script at your own risk. The author takes no responsibility for any damage that may arise from using this script.
