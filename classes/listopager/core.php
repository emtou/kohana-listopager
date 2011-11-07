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
  protected $_config = NULL;                               /** configuration array */
  protected $_loaded = FALSE;                              /** Is the configuration loaded ? */

  public $alias                            = '';           /** Alias of the ListoPager */
  public $config_filename                  = '';           /** Configuration file name */
  public $_default_config_filename         = 'listopager'; /** Default configuration file name */
  public $listo                            = NULL;         /** Listo instance */
  public $listo_actions                    = array();      /** List of Listo_Actions */
  public $listo_columns                    = array();      /** List of Listo columns' definitions */
  public $listo_multiaction_submit_classes = array();      /** List of CSS classes for Listo's MultiAction submit */
  public $listo_tablesorter                = TRUE;         /** Use jQuery's tablesorter */
  public $listo_view                       = '';           /** Listo's view filename */
  public $number_rows                      = 0;            /** Number of total rows */
  public $number_items_per_page            = 10;           /** Number of shown items per page */
  public $page_items                       = NULL;         /** List of current page's shown items */
  public $pagination                       = NULL;         /** Pagination instance */


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

    $this->_load($this->_default_config_filename);

    if ($this->config_filename != '')
    {
      $this->_load('listopager/'.$this->config_filename);
    }
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
   * Configures the inner Listo with defined columns
   *
   * @return null
   */
  protected function _init_listo_columns()
  {
    $alignments = array();
    $keys       = array();
    $titles     = array();

    foreach ($this->listo_columns as $column)
    {
      $alignments[] = (isset($column['align'])?$column['align']:'left');
      $keys[]       = $column['alias'];
      $titles[]     = $column['label'];

      if (isset($column['callback']))
      {
        $this->listo->add_table_callback($column['callback'], 'column', $column['alias']);
      }
    }
    $this->listo->set_column_attributes('align', $alignments);
    $this->listo->set_column_keys($keys);
    $this->listo->set_column_titles($titles);
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
   * Renders the javascript code for the inner Listo
   *
   * @return string javascript code
   */
  protected function _js_code_listo()
  {
    $js_code = $this->listo->js_code();

    if (count($this->listo_multiaction_submit_classes) > 0)
    {
      $js_code .= '$("input[name=doaction]")'.
                    '.addClass("'.implode(' ', $this->listo_multiaction_submit_classes).'");';
    }

    return $js_code;
  }


  /**
   * Renders the javascript code for jQuery's tablesorter plugin
   *
   * @return string javascript code
   */
  protected function _js_code_tablesorter()
  {
    if ( ! $this->listo_tablesorter)
      return '';

    $js_code = '$("#'.$this->alias.'")
                  .tablesorter({
                    widgets: ["zebra"]';

    $unsortable_columns = array();
    $count              = 0;

    if ($this->listo->has_multiactions())
    {
      $unsortable_columns[] = $count.': {sorter: false}';
      $count++;
    }

    foreach ($this->listo_columns as $column)
    {
      if (isset($column['sortable'])
          and ! $column['sortable'])
      {
        $unsortable_columns[] = $count.': {sorter: false}';
      }
      $count++;
    }

    if ($this->listo->has_soloactions())
    {
      $unsortable_columns[] = $count.': {sorter: false}';
    }

    if (count($unsortable_columns) > 0)
    {
      $js_code .= ', headers: {
                    '.implode(', ', $unsortable_columns).
                  '}';
    }

    $js_code .= '});';

    return $js_code;
  }


  /**
   * Load a listopager configuration file
   *
   * @param string $config_filename Configuration file name
   *
   * @return null
   *
   * @throws ListoPager_Exception Can't load listopager :alias: configuration file :fname not found
   */
  protected function _load($config_filename)
  {
    if ($config_filename != '')
    {
      $this->_config = Kohana::config($config_filename);
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

      $this->_load_listo_actions();
      $this->_load_listo_columns();
      $this->_load_listo_params();
    }
  }


  /**
   * Loads Listo actions' definitions from configuration file
   *
   * @return null
   *
   * @throws ListoPager_Exception Can't load listopager :alias: configuration variable listo.actions should be an array
   */
  protected function _load_listo_actions()
  {
    if (isset($this->_config['listo'])
        and isset($this->_config['listo']['actions']))
    {
      if ( ! is_array($this->_config['listo']['actions']))
      {
        throw new ListoPager_Exception(
          'Can\'t load listopager :alias: configuration variable listo.actions '.
          'should be an array',
          array('alias' => $this->alias)
        );
      }

      $this->listo_actions = $this->_config['listo']['actions'];
    }
  }


  /**
   * Loads Listo columns' definitions from configuration file
   *
   * @return null
   *
   * @throws ListoPager_Exception Can't load listopager :alias: configuration variable listo.columns should be an array
   */
  protected function _load_listo_columns()
  {
    if (isset($this->_config['listo'])
        and isset($this->_config['listo']['columns']))
    {
      if ( ! is_array($this->_config['listo']['columns']))
      {
        throw new ListoPager_Exception(
          'Can\'t load listopager :alias: configuration variable listo.columns '.
          'should be an array',
          array('alias' => $this->alias)
        );
      }

      $this->listo_columns = $this->_config['listo']['columns'];
    }
  }


  /**
   * Loads Listo's multiaction_submit_classes param definition from configuration file
   *
   * @return null
   *
   * @throws ListoPager_Exception Can't load listopager :alias:
   *                              configuration variable listo.params.multiaction_submit_classes should be an array
   */
  protected function _load_listo_param_multiaction_submit_classes()
  {
    if (isset($this->_config['listo']['params']['multiaction_submit_classes']))
    {
      if ( ! is_array($this->_config['listo']['params']['multiaction_submit_classes']))
      {
        throw new ListoPager_Exception(
          'Can\'t load listopager :alias: configuration variable listo.params.multiaction_submit_classes '.
          'should be an array',
          array('alias' => $this->alias)
        );
      }

      $this->listo_multiaction_submit_classes = $this->_config['listo']['params']['multiaction_submit_classes'];
    }
  }


  /**
   * Loads Listo's tablesorter param definition from configuration file
   *
   * @return null
   *
   * @throws ListoPager_Exception Can't load listopager :alias:
   *                              configuration variable listo.params.tablesorter should be a boolean
   */
  protected function _load_listo_param_tablesorter()
  {
    if (isset($this->_config['listo']['params']['tablesorter']))
    {
      if ( ! is_bool($this->_config['listo']['params']['tablesorter']))
      {
        throw new ListoPager_Exception(
          'Can\'t load listopager :alias: configuration variable listo.params.tablesorter '.
          'should be a boolean',
          array('alias' => $this->alias)
        );
      }

      $this->listo_tablesorter = $this->_config['listo']['params']['tablesorter'];
    }
  }


  /**
   * Loads Listo's view param definition from configuration file
   *
   * @return null
   *
   * @throws ListoPager_Exception Can't load listopager :alias:
   *                              configuration variable listo.params.view should be a string
   */
  protected function _load_listo_param_view()
  {
    if (isset($this->_config['listo']['params']['view']))
    {
      if ( ! is_string($this->_config['listo']['params']['view']))
      {
        throw new ListoPager_Exception(
          'Can\'t load listopager :alias: configuration variable listo.params.view '.
          'should be a string',
          array('alias' => $this->alias)
        );
      }

      $this->listo_view = $this->_config['listo']['params']['view'];
    }
  }


  /**
   * Loads Listo params' definitions from configuration file
   *
   * @return null
   *
   * @throws ListoPager_Exception Can't load listopager :alias: configuration params should be an array
   */
  protected function _load_listo_params()
  {
    if (isset($this->_config['listo'])
        and isset($this->_config['listo']['params']))
    {
      if ( ! is_array($this->_config['listo']['params']))
      {
        throw new ListoPager_Exception(
          'Can\'t load listopager :alias: configuration variable listo.columns '.
          'should be an array',
          array('alias' => $this->alias)
        );
      }

      $this->_load_listo_param_tablesorter();
      $this->_load_listo_param_view();
      $this->_load_listo_param_multiaction_submit_classes();
    }
  }


  /**
   * Performs last tweaks on the inner Listo before final rendering
   *
   * - sets the Listo's view
   * - add tablesorter CSS class to inner Listo's inner table
   *
   * @return null
   */
  protected function _pre_render_listo()
  {
    $this->_set_listo_view();

    if ($this->listo_tablesorter == TRUE)
    {
      $this->listo->table->set_attributes('class="tablesorter"', '');
    }
  }


  /**
   * Renders a message when nothing is to be shown by the inner Listo
   *
   * @return string empty string
   */
  protected function _render_empty_listo()
  {
    return '';
  }


  /**
   * Renders a message when nothing is to be shown by the inner Pagination
   *
   * @return string empty string
   */
  protected function _render_empty_pagination()
  {
    return '';
  }


  /**
   * Configures the Listo's view file with inner parameter
   *
   * @return null
   */
  protected function _set_listo_view()
  {
    if ($this->listo_view != '')
    {
      $this->listo->set_view($this->listo_view);
    }
  }


  /**
   * Initialises the inner Listo
   *
   * Chainable method
   *
   * @return this
   *
   * @todo Move this particular member to a ListoPager_Database specialised objet
   */
  public function init()
  {
    $this->_count_total_items();

    $this->_init_pagination();

    $this->_fetch_page_items();

    if (count($this->page_items) > 0)
    {
      $this->_init_listo_actions();
      $this->_init_listo_columns();
      $this->listo->set_data($this->page_items);
    }

    return $this;
  }


  /**
   * Javascript code for the inner Listo
   *
   * @return string javascript code
   */
  public function js_code()
  {
    if (count($this->page_items) == 0)
      return '';

    $js_code  = $this->_js_code_listo()."\n";
    $js_code .= $this->_js_code_tablesorter()."\n";

    return '$(function() {
              '.$js_code.'
           });';
  }


  /**
   * Renders the inner Listo
   *
   * @return string HTML
   */
  public function render_listo()
  {
    if (count($this->page_items) == 0)
      return $this->_render_empty_listo();

    $this->_pre_render_listo();

    return $this->listo->render();
  }


  /**
   * Renders the inner Pagination
   *
   * @return string HTML
   */
  public function render_pagination()
  {
    if (count($this->page_items) == 0)
      return $this->_render_empty_pagination();

    return $this->pagination->render();
  }


  /**
   * Sets the page shown elements
   *
   * @param array|Countable $elements list of elements to shown on current page
   *
   * @return null
   */
  public function set_page_items($elements)
  {
    $this->page_items = $elements;

    $this->number_rows = count($elements);
  }

}