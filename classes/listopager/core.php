<?php
/**
 * Declares ListoPager_Core helper
 *
 * PHP version 5
 *
 * @group listopager
 *
 * @category  ListoPager
 * @package   ListoPager
 * @author    mtou <mtou@charougna.com>
 * @copyright 2011 mtou
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      https://github.com/emtou/kohana-listopager/tree/master/classes/listopager/core.php
 */

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Provides ListoPager_Core helper
 *
 * PHP version 5
 *
 * @group listopager
 *
 * @category  ListoPager
 * @package   ListoPager
 * @author    mtou <mtou@charougna.com>
 * @copyright 2011 mtou
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      https://github.com/emtou/kohana-listopager/tree/master/classes/listopager/core.php
 */
abstract class ListoPager_Core
{
  protected $_config = NULL;               /** configuration array */
  protected $_loaded = FALSE;              /** Is the configuration loaded ? */

  public $alias                 = '';      /** Alias of the ListoPager */
  public $config_filename       = '';      /** Configuration file name */
  public $listo                 = NULL;    /** Listo instance */
  public $listo_actions         = array(); /** list of Listo_Actions */
  public $number_rows           = 0;       /** Number of total rows */
  public $number_items_per_page = 10;      /** Number of shown items per page */
  public $page_items            = NULL;    /** List of current page's shown items */
  public $pagination            = NULL;    /** Pagination instance */


  /**
   * Instanciate inner Listo with alias
   *
   * @return ListoPager instance
   *
   * @throws ListoPager_Exception Can't instanciate ListoPager: not alias has been set
   */
  public function __construct()
  {
    if ($this->alias == '')
    {
      throw new ListoPager_Exception(
        'Can\'t instanciate ListoPager: not alias has been set'
      );
    }
    $this->listo = Listo::factory($this->alias);

    $this->_load();
  }


  /**
   * Counts number of total items which could be displayed by the inner Listo
   *
   * Does nothing. Should be overloaded if not directly read from $this->number_rows variable
   *
   * @return null
   */
  protected function _count_total_items()
  {
  }


  /**
   * Fetches current page shown items from database
   *
   * Does nothing. Should be overloaded if items are not directly given to the ListoPager
   *
   * @return null
   *
   * @todo Move this member to a ListoPager_Database specialised object
   */
  protected function _fetch_page_items()
  {
  }


  /**
   * Configures the inner Listo with defined actions
   *
   * @return null
   */
  protected function _init_listo_actions()
  {
    foreach ($this->listo_actions as $action)
    {
      $this->listo->add_action($action);
    }
  }


  /**
   * Instanciates inner Pagination
   *
   * @return null
   */
  protected function _init_pagination()
  {
    $this->pagination = Pagination::factory(
        array(
          'total_items'    => $this->number_rows,
          'items_per_page' => $this->number_items_per_page,
        )
    );
  }


  /**
   * Load the listopager from configuration file
   *
   * @return null
   *
   * @throws ListoPager_Exception Can't load listopager :alias: configuration file :fname not found
   */
  protected function _load()
  {
    if ($this->_loaded)
    {
      return;
    }

    if ($this->config_filename != '')
    {
      $config_filename = 'listopager/'.$this->config_filename;
      $this->_config   = Kohana::config($config_filename);
      if ( ! $this->_config)
      {
        throw new ListoPager_Exception(
          'Can\'t load listopager :alias: configuration file :fname not found',
          array(
            'alias' => $this->alias,
            'fname' => $config_filename,
          )
        );
      }

      $this->_load_actions();
    }

    $this->_loaded = TRUE;
  }


  /**
   * Loads actions from configuration file
   *
   * @return null
   *
   * @throws ListoPager_Exception Can't load listopager :alias: configuration variable actions should be an array
   */
  protected function _load_actions()
  {
    if (isset($this->_config['actions']))
    {
      if ( ! is_array($this->_config['actions']))
      {
        throw new ListoPager_Exception(
          'Can\'t load listopager :alias: configuration variable actions '.
          'should be an array',
          array('alias' => $this->alias)
        );
      }

      $this->listo_actions = $this->_config['actions'];
    }
  }

}