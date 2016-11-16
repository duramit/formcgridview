<?php
/**
 * This's not my extension!!!
 */

use yii\grid\GridView;

class formCGridView extends CGridView{


    /**
     * @var array The initial parameters
     */
    public $fillable=array();

    /**
     * @var array Simple array colname => html content
     */
    private $filcols = array();

    /**
     * @var string The ajax action
     */
    public $fillAction;

    /**
     * @var string The html button
     */
    public $fillButton;

    /**
     * @var boolean Is fill field filtering field too ?
     */
    public $fillIsFilter = false;

    /**
     * Initialisation of the widget
     */
    public function init() {
        if (count($this->fillable)>0)
        {
            if (!isset($this->fillable['position']))
            {
                $this->fillable['position'] = 'bottom';
            }
            if (!isset($this->fillable['CButtonColumn']))
            {
                $this->fillAction = $this->owner;
                $this->filButton = '<a href="'.$this->fillAction.'">'.Yii::t('main', 'Add').'</a>';
            }
            else
            {
                $this->fillAction = $this->fillable['CButtonColumn']['action'];
                $this->fillButton = $this->fillable['CButtonColumn']['button'];
            }
            foreach ($this->fillable['columns'] as $k => $col)
            {
                $this->filcols[$col['name']] = $col['value'];
            }
            if (isset($this->fillable['fillIsFilter'])) {
                $this->fillIsFilter = true;
            }

        }
        parent::init();

    }

    /**
     * Renders the form after the header
     * used when position = top
     */
    public function renderTableHeader()
    {
        parent::renderTableHeader();
        if (count($this->fillable)>0 && $this->fillable['position'] == 'top')
        {
            $this->renderFillable();
        }
    }

    /**
     * Renders the form after the tbody
     * used by default
     */
    public function renderTableBody()
    {
        parent::renderTableBody();
        if (count($this->fillable)>0 && $this->fillable['position'] == 'bottom')
        {
            $this->renderFillable();
        }
    }

    /**
     * Renders the new line
     */
    public function renderFillable()
    {
        $this->_renderScript();
        if ($this->fillIsFilter===true) {
            echo '<tr id="update-', $this->id, '" class="filters">';
        } else {
            echo '<tr id="update-', $this->id, '">';
        }

        foreach($this->columns as $column) {

            if (is_a($column, 'CDataColumn') && isset($this->filcols[$column->name]))
            {
                echo '<td>', $this->filcols[$column->name], '</td>';
            }
            elseif(is_a($column, 'CButtonColumn') && isset($this->fillButton))
            {
                echo '<td>', $this->fillButton, '</td>';
                //echo '<td>', CHtml::ajaxSubmitButton('add', $this->fillAction, array('update', $this->id)), '</td>';
            }
            else
            {
                echo '<td></td>';
            }
        }
        echo '</tr>', PHP_EOL;
    }

    /**
     * Register the ajax script
     */
    private function _renderScript()
    {
        Yii::app()->clientScript->registerScript("fillable", "
        jQuery(document).on('click', '#update-".$this->id." td a', function() {
            var th = this,
            ajaxFill = function() {};
            form = $('#update-".$this->id."').find(':input')
            data = $(form).serialize();
            $.ajax({
                type: 'POST',
                url: '".$this->fillAction."',
                data:data,
                dataType:'html',
                success: function(data) {
                    jQuery('#".$this->id."').yiiGridView('update');
                    ajaxFill(th, true, data);
                },
                error: function(XHR) {
                    return ajaxFill(th, false, XHR);
                }
             });
             return false;
        });");
    }

}