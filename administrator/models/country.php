<?php

defined('_JEXEC') or die;

class CatalogueModelCountry extends JModelAdmin
{

    public function getTable($type = 'Countries', $prefix = 'CatalogueTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_catalogue.country', 'country', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    public function save($data)
    {
        if (isset($data['alias']) && empty($data['alias'])) {
            $data['alias'] = ru_RULocalise::transliterate($data['country_name']);
            $data['alias'] = preg_replace('#\W#', '-', $data['alias']);
            $data['alias'] = preg_replace('#[-]+#', '-', $data['alias']);
        }

        return parent::save($data);
    }

    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app = JFactory::getApplication();
        $data = $app->getUserState('com_catalogue.edit.country.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        return $item;
    }

}
