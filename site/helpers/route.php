<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_catalogue
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


abstract class CatalogueHelperRoute
{
    protected static $lookup = array();

    protected static $lang_lookup = array();

    /**
     * @param   integer  The route of the content item
     */
    public static function getItemRoute($id, $catid = 0, $language = 0)
    {
        $needles = array(
            'item' => array((int)$id)
        );

        // Create the link
        $link = 'index.php?option=com_catalogue&view=item&id=' . $id;

        if ((int)$catid > 1) {
            $categories = JCategories::getInstance('Catalogue');
            $category = $categories->get((int)$catid);

            if ($category) {
                $needles['category'] = array_reverse($category->getPath());
                $needles['categories'] = $needles['category'];
                $link .= '&cid=' . $catid;
            }
        }

        if ($language && $language != "*" && JLanguageMultilang::isEnabled()) {
            self::buildLanguageLookup();

            if (isset(self::$lang_lookup[$language])) {
                $link .= '&lang=' . self::$lang_lookup[$language];
                $needles['language'] = $language;
            }
        }

        if ($item = self::_findItem($needles)) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    protected static function buildLanguageLookup()
    {
        if (count(self::$lang_lookup) == 0) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('a.sef AS sef')
                ->select('a.lang_code AS lang_code')
                ->from('#__languages AS a');

            $db->setQuery($query);
            $langs = $db->loadObjectList();

            foreach ($langs as $lang) {
                self::$lang_lookup[$lang->lang_code] = $lang->sef;
            }
        }
    }

    protected static function _findItem($needles = null)
    {
        $app = JFactory::getApplication();
        $menus = $app->getMenu('site');
        $language = isset($needles['language']) ? $needles['language'] : '*';

        // Prepare the reverse lookup array.
        if (!isset(self::$lookup[$language])) {
            self::$lookup[$language] = array();

            $component = JComponentHelper::getComponent('com_catalogue');

            $attributes = array('component_id');
            $values = array($component->id);

            if ($language != '*') {
                $attributes[] = 'language';
                $values[] = array($needles['language'], '*');
            }

            $items = $menus->getItems($attributes, $values);

            foreach ($items as $item) {
                if (isset($item->query) && isset($item->query['view'])) {
                    $view = $item->query['view'];

                    if (!isset(self::$lookup[$language][$view])) {
                        self::$lookup[$language][$view] = array();
                    }

                    if (isset($item->query['cid'])) {
                        /**
                         * Here it will become a bit tricky
                         * language != * can override existing entries
                         * language == * cannot override existing entries
                         */
                        if (!isset(self::$lookup[$language][$view][$item->query['cid']]) || $item->language != '*') {
                            self::$lookup[$language][$view][$item->query['cid']] = $item->id;
                        }
                    }
                    if (isset($item->query['id'])) {
                        /**
                         * Here it will become a bit tricky
                         * language != * can override existing entries
                         * language == * cannot override existing entries
                         */
                        if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*') {
                            self::$lookup[$language][$view][$item->query['id']] = $item->id;
                        }
                    }
                }
            }
        }

        if ($needles) {
            foreach ($needles as $view => $ids) {
                if (isset(self::$lookup[$language][$view])) {
                    foreach ($ids as $id) {
                        if (isset(self::$lookup[$language][$view][(int)$id])) {
                            return self::$lookup[$language][$view][(int)$id];
                        }
                    }
                }
            }
        }

        // Check if the active menuitem matches the requested language
        $active = $menus->getActive();

        if ($active && $active->component == 'com_catalogue' && ($language == '*' || in_array($active->language, array('*', $language)) || !JLanguageMultilang::isEnabled())) {
            return $active->id;
        }

        // If not found, return language specific home link
        $default = $menus->getDefault($language);

        return !empty($default->id) ? $default->id : null;
    }

    public static function getCategoryRoute($catid, $language = 0)
    {
        if ($catid instanceof JCategoryNode) {
            $id = $catid->id;
            $category = $catid;
        } else {
            $id = (int)$catid;
            $category = JCategories::getInstance('Catalogue')->get($id);
        }

        if ($id < 1 || !($category instanceof JCategoryNode)) {
            $link = '';
        } else {
            $needles = array();
            $link = 'index.php?option=com_catalogue&view=category&cid=' . $id;
            $catids = array_reverse($category->getPath());
            $needles['category'] = $catids;
            $needles['categories'] = $catids;

            if ($language && $language != "*" && JLanguageMultilang::isEnabled()) {
                self::buildLanguageLookup();

                if (isset(self::$lang_lookup[$language])) {
                    $link .= '&lang=' . self::$lang_lookup[$language];
                    $needles['language'] = $language;
                }
            }

            if ($item = self::_findItem($needles)) {
                $link .= '&Itemid=' . $item;
            }
        }

        return $link;
    }

    public static function getFormRoute($id)
    {
        // Create the link
        if ($id) {
            $link = 'index.php?option=com_catalogue&task=item.edit&a_id=' . $id;
        } else {
            $link = 'index.php?option=com_catalogue&task=item.edit&a_id=0';
        }

        return $link;
    }
}