<?php
namespace DbMenu;

/**
 * FuelPHP DbMenu Package
 *
 * @author     Phil Foulston
 * @version    1.0
 * @package    Fuel
 * @subpackage DbMenu
 */

class DbMenu {

    /**
	* @var table name
	*/
	public static $table                = null;
        public static $bootstrap            = null;
        public static $ul_class             = null;
        public static $first_class          = null;
        public static $second_class         = null;
        public static $dropdown_icon        = null;
        public static $first_link_class     = null;
        public static $first_link_toggle    = null;

    public static function _init()
    {
            \Config::load('dbmenu', true);
            static::$table              = \Config::get('dbmenu.db.table', 'dbmenu');
            static::$bootstrap          = \Config::get('dbmenu.bootstrap.active');
            static::$ul_class           = \Config::get('dbmenu.bootstrap.ul_class');
            static::$first_class        = \Config::get('dbmenu.bootstrap.first_class');
            static::$second_class       = \Config::get('dbmenu.bootstrap.second_class');
            static::$dropdown_icon      = \Config::get('dbmenu.bootstrap.dropdown_icon');
            static::$first_link_class   = \Config::get('dbmenu.bootstrap.first_link_class');
            static::$first_link_toggle  = \Config::get('dbmenu.bootstrap.first_link_toggle');
    }

    public static function build($menu_name = 'main', $bootstrap=null)
    {
        $bootstrap = (isset($bootstrap)) ? $bootstrap : static::$bootstrap;
        $menu_data = static::populate_menu($menu_name);
        $html = static::build_menu($menu_data, 0, false, $bootstrap);
        return substr($html, 0, strlen($html)-5); // strip the last </ul> from the string
    }

    private static function populate_menu($menu_name)
    {
        $menu_data = array('parents' => array(), 'items' => array());

        $result = \DB::select('*')
				->from(static::$table)
                ->where('menu_name', $menu_name)
				->order_by('parent')
				->order_by('position')
                ->order_by('title')
                ->execute()
                ->as_array();

        foreach ($result as $menu_item)
        {
            $menu_data['items'][$menu_item['id']] = $menu_item;
            $menu_data['parents'][$menu_item['parent']][] = $menu_item['id'];
        }

        return $menu_data;
    }

    /*
     * Source:  http://twitter.github.com/bootstrap/components.html#dropdowns
     *          http://twitter.github.com/bootstrap/components.html#navbar 
     */
    private static function build_menu($menu_data, $parent = 0, $sub = false, $bootstrap)
    {
        $html = "";
        if (isset($menu_data['parents'][$parent]))
        {
            foreach ($menu_data['parents'][$parent] as $itemId)
            {
                //check for sub items
                if(!isset($menu_data['parents'][$itemId]))
                {
                    //the current item has no subitems
                    $html .= "<li><a href='/".$menu_data['items'][$itemId]['link']."'>".$menu_data['items'][$itemId]['title']."</a></li>";
                }
                else
                {
                    //the current item has one more subitems
                    if($parent==0)
                    { 
                        //set the dropdown code for the 1st level
                        $html .= '<li '.(($bootstrap) ? static::$first_class : "").'><a href="'.$menu_data['items'][$itemId]['link'].'" '.(($bootstrap) ? static::$first_link_class : "").' '.(($bootstrap) ? static::$first_link_toggle : "").'>'.$menu_data['items'][$itemId]['title'].''.(($bootstrap) ? static::$dropdown_icon : "").'</a>';
                    }
                    else
                    {
                        //set the dropdown code for the 2nd level
                        $html .= '<li '.(($bootstrap) ? static::$second_class : "").'><a href="'.$menu_data['items'][$itemId]['link'].'">'.$menu_data['items'][$itemId]['title'].''.(($bootstrap) ? static::$dropdown_icon : "").'</a>';
                    }
                
                    if ($parent === 0 || ($parent!=0 && $sub==true))
                    {
                        $html .= '<ul '.(($bootstrap) ? static::$ul_class : "").'>';
                    }
                    
                    $html .= static::build_menu($menu_data, $itemId, true, $bootstrap);
                    $html .= "</li>";
                }
            }
            
            if ($sub == true)
            {
                $html .= "</ul>";
            }
        }
        
        return $html;
    }

}