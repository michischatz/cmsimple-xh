<?php
/* utf8-marker = äöüß */
/**
 * Page-Data - Module page_data_model
 *
 * Part of the Pluginloader of $CMSIMPLE_XH_VERSION$
 *
 * @package	  XH
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2012 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license	  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version	  $CMSIMPLE_XH_VERSION$, $CMSIMPLE_XH_BUILD$
 * @version   $Id: page_data_model.php 315 2012-10-31 00:09:01Z cmb69 $
 * @link	  http://cmsimple-xh.org/
 * @author    Martin Damken
 * @link      http://www.zeichenkombinat.de/
 */


/**
 * Handles the page-data-array including reading and writing of the files.
 *
 * @package XH
 * @access  public
 */
class PL_Page_Data_Model
{
    /**
     * The page headings (a copy of $h).
     *
     * @var array
     */
    var $headings;

    /**
     * The list of page data fields.
     *
     * @var array
     */
    var $params;

    /**
     * The page data.
     *
     * @var array
     */
    var $data;

    /**
     * The page data of the latest deleted page (recycle bin).
     *
     * @var array
     */
    var $temp_data;

    /**
     * The filenames of the views of page data tabs.
     *
     * @var array
     */
    var $tabs;

    /**
     * @global array
     * @global array
     * @param  array $h  The page headings.
     * @param  string $contentHead  The head element of the contents file.
     * @return void
     */
    function PL_Page_Data_Model($h, $contentHead)
    {
        global $pth, $c;

        $this->headings = $h;
        if (preg_match('/<\?php(.*?)\?>/isu', $contentHead, $m)) {
            eval($m[1]);
        }
        $this->params = isset($page_data_fields)
            ? $page_data_fields
            : array('url', 'last_edit');
        $this->temp_data = isset($temp_data) ? $temp_data : array();
        $page_data = array();
        foreach ($c as $i => $j) {
            if (preg_match('/<\?php(.*?)\?>/is', $j, $m)) {
                eval($m[1]);
                $c[$i] = preg_replace('/<\?php.*?\?>/is', '', $j);
            }
        }
        $this->data = $page_data;
        $this->read();
    }

    /**
     * Fixes the page data after reading.
     *
     * @todo should better be renamed as it doesn't read the page data since XH 1.6
     *
     * @return void
     */
    function read()
    {
        foreach ($this->headings as $id => $value) {
            foreach ($this->params as $param) {
                if (!isset($this->data[$id][$param])) {
                    switch ($param) {
                    case 'url':
                        $this->data[$id][$param] = uenc(strip_tags($value));
                        break;
                    default:
                        $this->data[$id][$param] = '';
                    }
                }
            }
        }
    }

    /**
     * Replaces the existing page data.
     *
     * @param  array $data  The new page data.
     * @return bool  Whether $data was not null.
     */
    function refresh($data = null)
    {
        if (isset($data)){
            $this->data = $data;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Registers a page data field.
     *
     * @param  string $field
     * @return void
     */
    function add_param($field)
    {
        $this->params[] = $field;
        $this->save();
    }

    /**
     * Registers a page data tab.
     *
     * @param  string $title  The title of the tab.
     * @param  string $view_file  The filename of the view.
     * @return void
     */
    function add_tab($title, $view_file)
    {
        $this->tabs[$title] = $view_file;
    }

    /**
     * Returns the page data of a single page.
     *
     * @param  int $key  The index of the page.
     * @return array
     */
    function find_key($key)
    {
        return $key >= 0 ? $this->data[$key] : null;
    }

    /**
     * Returns the page data of all pages which contain a value in a field.
     *
     * @param  string $field  The name of the field.
     * @param  mixed $value  The value to look for.
     * @return array
     */
    function find_field_value($field, $value)
    {
        $results = array();
        foreach ($this->data as $id => $page) {
            // TODO: use strpos() here; it's faster
            if (strstr($page[$field], $value)) {
                $results[$id] = $page;
            }
        }
        return $results;
    }

    /**
     * Returns the page data of all pages which contain a value in a list field.
     *
     * @param  string $field  The name of the field.
     * @param  string $value  The value to look for.
     * @param  string $separator  The list item separator.
     * @return array
     */
    function find_arrayfield_value($field, $value, $separator)
    {
        $results = array();
        foreach ($this->data as $id => $page) {
            $array = explode($separator, $page[$field]);

            foreach ($array as $page_data) {
                if ($value == trim($page_data)) {
                    $results[$id] = $page;
                }
            }
        }
        return $results;
    }

    /**
     * Returns the sorted page data of all pages,
     * which contain a value in a (list) field.
     *
     * @param  string $field  The name of the field.
     * @param  string $value  The value to look for.
     * @param  string $sort_key  The name of the field to sort by.
     * @param  int $sort_flag  The sort options as for array_multisort().
     * @param  string $separator  The list item separator.
     * @return array
     */
    function find_field_value_sortkey($field, $value, $sort_key, $sort_flag,
                                      $separator)
    {
        if ($separator) {
            $results = $this->find_arrayfield_value($field, $value, $separator);
        } else {
            $results = $this->find_field_value($field, $value);
        }
        $temp = array();
        $ids = array();
        foreach ($results as $key => $value) {
            $temp[] = $value[$sort_key];
            $ids[] = $key;
        }
        array_multisort($temp, $sort_flag, $ids);
        $results = array();
        if (is_array($ids) && count($ids) > 0) {
            foreach ($ids as $id) {
                $results[$id] = $this->data[$id];
            }
        }
        return $results;
    }


    /**
     * Returns the page data for a new page.
     *
     * @param  array $params  Default page data.
     * @return array
     */
    function create($params = null)
    {
        $clean = array();
        foreach ($this->params as $field) {
            $clean[$field] = '';
        }
        $page = array_merge($clean, $params);
        return $page;
    }

    /**
     * Replaces the page data of a single page.
     *
     * @param  array $pages  The new page data.
     * @param  int $index  The index of the page.
     * @return void
     */
    function replace($pages, $index)
    {
        array_splice($this->data, $index, 1, $pages);
        $this->save();
    }


    /**
     * Stores page data in the recycle bin.
     *
     * @param  array $page
     * @return void
     */
    function store_temp($page)
    {
        foreach ($page as $field => $value) {
            if (in_array($field, $this -> params)) {
                $this->temp_data[$field] = $value;
            }
        }
    }

    /**
     * Deletes the page data of a single page.
     *
     * @param  int $key  The index of the page.
     * @return void
     */
    function delete($key)
    {
        array_splice($this->data, $key, 1);
        $this->save();
    }

    /**
     * Updates the page data of a single page.
     *
     * @param  int $key  The index of the page.
     * @param  array $params  The dictionary of fields to update.
     * @return void
     */
    function update_key($key, $params)
    {
        foreach ($params as $field => $value) {
            $this->data[$key][$field] = $value;
        }
        $this->save();
    }

    /**
     * Saves the page data.
     *
     * @return void
     */
    function save()
    {
        XH_saveContents();
    }
}

?>
