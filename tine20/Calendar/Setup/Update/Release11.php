<?php
/**
 * Tine 2.0
 *
 * @package     Calendar
 * @subpackage  Setup
 * @license     http://www.gnu.org/licenses/agpl.html AGPL3
 * @copyright   Copyright (c) 2015-2018 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Cornelius Weiß <c.weiss@metaways.de>
 */
class Calendar_Setup_Update_Release11 extends Setup_Update_Abstract
{
    /**
     * update to 11.1
     * - add polls & poll_id
     */
    public function update_0()
    {
        if (!$this->_backend->columnExists('poll_id', 'cal_events')) {
            $declaration = new Setup_Backend_Schema_Field_Xml('
            <field>
                <name>poll_id</name>
                <type>text</type>
                <length>40</length>
            </field>');
            $this->_backend->addCol('cal_events', $declaration);

            $declaration = new Setup_Backend_Schema_Index_Xml('
            <index>
                <name>poll_id</name>
                <field>
                    <name>poll_id</name>
                </field>
            </index>');
            $this->_backend->addIndex('cal_events', $declaration);
        }

        $this->updateSchema('Calendar', [
            Calendar_Model_Poll::class,
        ]);

        $this->setTableVersion('cal_events', 15);
        $this->setApplicationVersion('Calendar', '11.1');
    }

    /**
     * update to 11.2
     *
     * Update export templates
     *
     * @return void
     * @throws \Tinebase_Exception_InvalidArgument
     * @throws Tinebase_Exception_NotFound
     */
    public function update_1()
    {
        Setup_Controller::getInstance()->createImportExportDefinitions(Tinebase_Application::getInstance()->getApplicationByName('Calendar'), Tinebase_Core::isReplicationSlave());

        $this->setApplicationVersion('Calendar', '11.2');
    }

    /**
     * update to 11.3
     *
     * Update export templates
     *
     * @return void
     * @throws \Tinebase_Exception_InvalidArgument
     * @throws Tinebase_Exception_NotFound
     */
    public function update_2()
    {
        Setup_Controller::getInstance()->createImportExportDefinitions(Tinebase_Application::getInstance()->getApplicationByName('Calendar'), Tinebase_Core::isReplicationSlave());

        $this->setApplicationVersion('Calendar', '11.3');
    }

    /**
     * update to 11.4
     *
     * Update export templates
     *
     * @return void
     * @throws \Tinebase_Exception_InvalidArgument
     * @throws Tinebase_Exception_NotFound
     */
    public function update_3()
    {
        Setup_Controller::getInstance()->createImportExportDefinitions(Tinebase_Application::getInstance()->getApplicationByName('Calendar'), Tinebase_Core::isReplicationSlave());

        $this->setApplicationVersion('Calendar', '11.4');
    }

    /**
     * update to 11.5
     *
     * Update export templates
     *
     * @return void
     * @throws \Tinebase_Exception_InvalidArgument
     * @throws Tinebase_Exception_NotFound
     */
    public function update_4()
    {
        Setup_Controller::getInstance()->createImportExportDefinitions(Tinebase_Application::getInstance()->getApplicationByName('Calendar'), Tinebase_Core::isReplicationSlave());

        $this->setApplicationVersion('Calendar', '11.5');
    }

    /**
     * force activesync calendar resync for iOS devices
     */
    public function update_5()
    {
        $release8 = new Calendar_Setup_Update_Release8($this->_backend);
        $release8->update_11();
        $this->setApplicationVersion('Calendar', '11.6');
    }

    /**
     * update to 11.7
     * Calendar_Model_ResourceGrants change
     */
    public function update_6()
    {
        $containerController = Tinebase_Container::getInstance();
        $resourceController = Calendar_Controller_Resource::getInstance();
        $resourceController->doContainerACLChecks(false);
        $resources = $resourceController->getAll();

        /** @var Calendar_Model_Resource $resource */
        foreach ($resources as $resource) {
            $container = $containerController->getContainerById($resource->container_id);
            if (!isset($container->xprops()['Tinebase']['Container']['GrantsModel'])) {
                $container->xprops()['Tinebase']['Container']['GrantsModel'] = Calendar_Model_ResourceGrants::class;
                $container->xprops()['Calendar']['Resource']['resource_id'] = $resource->getId();
                /** @var Tinebase_Model_Container $container */
                $container = $containerController->update($container);

                $grants = $containerController->getGrantsOfContainer($container, true);
                /** @var Calendar_Model_ResourceGrants $grant */
                foreach ($grants as $grant) {
                    if ($grant->{Tinebase_Model_Grants::GRANT_ADMIN}) {
                        foreach (Calendar_Model_ResourceGrants::getAllGrants() as $grantName) {
                            $grant->{$grantName} = true;
                        }
                    } else {
                        if ($grant->{Tinebase_Model_Grants::GRANT_ADD}) {
                            $grant->{Calendar_Model_ResourceGrants::EVENTS_ADD} = true;
                        }
                        if ($grant->{Tinebase_Model_Grants::GRANT_DELETE}) {
                            $grant->{Calendar_Model_ResourceGrants::EVENTS_DELETE} = true;
                        }
                        if ($grant->{Tinebase_Model_Grants::GRANT_EDIT}) {
                            $grant->{Calendar_Model_ResourceGrants::EVENTS_EDIT} = true;
                            $grant->{Calendar_Model_ResourceGrants::RESOURCE_EDIT} = true;
                        }
                        if ($grant->{Tinebase_Model_Grants::GRANT_EXPORT}) {
                            $grant->{Calendar_Model_ResourceGrants::EVENTS_EXPORT} = true;
                            $grant->{Calendar_Model_ResourceGrants::RESOURCE_EXPORT} = true;
                        }
                        if ($grant->{Calendar_Model_EventPersonalGrants::GRANT_FREEBUSY}) {
                            $grant->{Calendar_Model_ResourceGrants::EVENTS_FREEBUSY} = true;
                        }
                        if ($grant->{Tinebase_Model_Grants::GRANT_EXPORT}) {
                            $grant->{Calendar_Model_ResourceGrants::EVENTS_EXPORT} = true;
                            $grant->{Calendar_Model_ResourceGrants::RESOURCE_EXPORT} = true;
                        }
                        if ($grant->{Tinebase_Model_Grants::GRANT_READ}) {
                            $grant->{Calendar_Model_ResourceGrants::EVENTS_READ} = true;
                            $grant->{Calendar_Model_ResourceGrants::RESOURCE_READ} = true;
                            $grant->{Calendar_Model_ResourceGrants::EVENTS_FREEBUSY} = true;
                            $grant->{Calendar_Model_ResourceGrants::RESOURCE_INVITE} = true;
                        }
                        if ($grant->{Tinebase_Model_Grants::GRANT_SYNC}) {
                            $grant->{Calendar_Model_ResourceGrants::EVENTS_SYNC} = true;
                            $grant->{Calendar_Model_ResourceGrants::RESOURCE_SYNC} = true;
                        }
                    }
                }
                $resource->grants = $grants->toArray();
                $resourceController->update($resource);
            }
        }

        Calendar_Controller_Resource::destroyInstance();

        $this->setApplicationVersion('Calendar', '11.7');
    }

    /**
     * update to 11.8
     * Calendar_Model_EventPersonalGrants change
     */
    public function update_7()
    {
        $containers = Tinebase_Container::getInstance()->search(new Tinebase_Model_ContainerFilter([
            ['field' => 'application_id', 'operator' => 'equals', 'value' => Tinebase_Application::getInstance()
                ->getApplicationByName('Calendar')->getId()],
            ['field' => 'model', 'operator' => 'equals', 'value' => Calendar_Model_Event::class],
            ['field' => 'type', 'operator' => 'equals', 'value' => Tinebase_Model_Container::TYPE_PERSONAL],
        ]));

        /** @var Tinebase_Model_Container $container */
        foreach ($containers as $container) {
            $container->xprops()['Tinebase']['Container']['GrantsModel'] = Calendar_Model_EventPersonalGrants::class;
            Tinebase_Container::getInstance()->update($container);
        }

        $this->setApplicationVersion('Calendar', '11.8');
    }

    /**
     * update to 11.9
     */
    public function update_8()
    {
        $this->updateKeyFieldIcon(Calendar_Config::getInstance(), Calendar_Config::ATTENDEE_STATUS);
        $this->updateKeyFieldIcon(Calendar_Config::getInstance(), Calendar_Config::EVENT_STATUS);

        $this->setApplicationVersion('Calendar', '11.9');
    }

    /**
     * update to 12.0
     *
     * @return void
     */
    public function update_9()
    {
        $this->setApplicationVersion('Calendar', '12.0');
    }
}
