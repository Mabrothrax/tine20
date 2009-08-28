<?php
/**
 * Tine 2.0
 *
 * @package     Felamimail
 * @subpackage  Setup
 * @license     http://www.gnu.org/licenses/agpl.html AGPL3
 * @copyright   Copyright (c) 2009 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Philipp Schuele <p.schuele@metaways.de>
 * @version     $Id: Release0.php 10122 2009-08-21 10:23:50Z p.schuele@metaways.de $
 */

class Felamimail_Setup_Update_Release2 extends Setup_Update_Abstract
{
    /**
     * update function (2.0 -> 2.1)
     * - rename (stmp_)secure_connection to ssl
     */    
    public function update_0()
    {
        $fields = array(
                'secure_connection' => '<field>
                    <name>ssl</name>
                    <type>enum</type>
                    <value>none</value>
                    <value>TLS</value>
                    <value>SSL</value>
                </field>',
                'smtp_secure_connection' => '<field>
                    <name>smtp_ssl</name>
                    <type>enum</type>
                    <value>none</value>
                    <value>TLS</value>
                    <value>SSL</value>
                </field>');
        
        foreach ($fields as $oldname => $field) {
            $declaration = new Setup_Backend_Schema_Field_Xml($field);
            $this->_backend->alterCol('felamimail_account', $declaration, $oldname);
        }
        
        $this->setApplicationVersion('Felamimail', '2.1');
        $this->setTableVersion('felamimail_account', '7');
    }
}
