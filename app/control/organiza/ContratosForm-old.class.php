<?php
/**
 * ContratosForm Master/Detail
 * @author  Marcos Rodrigo Jung Alves
 */
class ContratosForm-old extends TPage
{
    protected $form; // form
    protected $formFields;
    protected $detail_list;

    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();

        // creates the form
        $this->form = new TForm('form_Contrato');
        $this->form->class = 'tform'; // CSS class
        $this->form->style = 'max-width:700px'; // style
        parent::include_css('app/resources/custom-frame.css');

        $table_master = new TTable;
        $table_master->width = '100%';

        $table_master->addRowSet( new TLabel('Casamento'), '', '')->class = 'tformtitle';

        // add a table inside form
        $table_general = new TTable;
        $table_detail  = new TTable;
        $table_general-> width = '100%';
        $table_detail-> width  = '100%';

        $frame_general = new TFrame;
        $frame_general->setLegend('Casamento de:');
        $frame_general->style = 'background:whiteSmoke';
        $frame_general->add($table_general);

        $table_master->addRow()->addCell( $frame_general )->colspan=2;
        $row = $table_master->addRow();
        $row->addCell( $table_detail );

        $this->form->add($table_master);

        // master fields
        $id = new THidden('id');
        $noiva = new TEntry('noiva');
        $noivo = new TEntry('noivo');
        $orcamento = new TEntry('orcamento');

        $id->setSize('100%');
        $noiva->setSize('100%');
        $noivo->setSize('100%');
        $orcamento->setSize('100%');

        if (!empty($id))
        {
            $id->setEditable(FALSE);
            $noivo->setEditable(FALSE);
            $noiva->setEditable(FALSE);
            $orcamento->setEditable(FALSE);
        }

        // detail fields
        $detail_id = new THidden('detail_id');
        // new TDBCombo('tipo', 'organizamc', 'TipoFornecedor', 'id', 'descricao', 'descricao');
        $detail_tipo_fornecedor = new TDBCombo('detail_tipo_fornecedor', 'organizamc', 'TipoFornecedor', 'id', 'descricao', 'descricao');
        // $municipio = new TDBCombo('municipio', 'organizamc', 'Cidades', 'id', 'nome', 'nome');
        $detail_nome_fornecedor = new TDBCombo('detail_nome_fornecedor', 'organizamc', 'Fornecedor', 'id', 'nome', 'nome');
        $detail_valor = new TEntry('detail_valor');
        $detail_obs = new TText('detail_obs');

        $detail_tipo_fornecedor->setSize('100%');
        $detail_nome_fornecedor->setSize('100%');
        $detail_valor->setSize('100%');
        $detail_obs->setSize('100%');

        /** samples
         $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( 100, 40 ); // set size
         **/

        // master
        $table_general->addRowSet( $id );
        $table_general->addRowSet( new TLabel('Noiva'), $noiva );
        $table_general->addRowSet( new TLabel('Noivo'), $noivo );
        $table_general->addRowSet( new TLabel('Orcamento'), $orcamento );

         // detail
        $frame_details = new TFrame();
        $frame_details->setLegend('Lançamento de novo CONTRATO');
        $row = $table_detail->addRow();
        $row->addCell($frame_details);

        $btn_save_detail = new TButton('btn_save_detail');
        $btn_save_detail->setAction(new TAction(array($this, 'onSaveDetail')), 'Adicionar novo Contrato');
        $btn_save_detail->setImage('fa:save');

        $table_details = new TTable;
        $frame_details->add($table_details);

        $table_details->addRowSet( '', $detail_id );
        $table_details->addRowSet( new TLabel('Tipo Fornecedor'), $detail_tipo_fornecedor );
        $table_details->addRowSet( new TLabel('Nome Fornecedor'), $detail_nome_fornecedor );
        $table_details->addRowSet( new TLabel('Valor'), $detail_valor );
        $table_details->addRowSet( new TLabel('Obs'), $detail_obs );

        $table_details->addRowSet( $btn_save_detail );


        // creates a Datagrid
        $this->detail_list = new TDataGrid;
        $this->detail_list = new BootstrapDatagridWrapper($this->detail_list);
        $this->detail_list->style = 'width: 100%';
        $this->detail_list->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');


        // creates the datagrid columns
        // $column_id = new TDataGridColumn('id', 'Id', 'right');
        // $column_id_casamento = new TDataGridColumn('dados_casamento->noiva', 'Noiva', 'right');
        // $column_id_usuario = new TDataGridColumn('user_nome->name', 'Usuário', 'right');

        //Actions
        // $this->detail_list->addQuickColumn('Edit', 'edit', 'left', 10);
        // $this->detail_list->addQuickColumn('Del', 'delete', 'left', 10);
        $column_edit = new TDataGridColumn('edit', 'Edit', 'right');
        $column_del = new TDataGridColumn('delete', 'Del', 'right');

        //Columns
        $column_tipo_fornecedor = new TDataGridColumn('tipo_fornecedor', 'Tipo Fornecedor', 'right');
        $column_nome_fornecedor = new TDataGridColumn('nome_fornecedor', 'Nome Fornecedor', 'right');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'right');
        // $column_obs = new TDataGridColumn('obs', 'Obs', 'left');

        // add the columns to the DataGrid
        // $this->detail_list->addColumn($column_id);
        // $this->detail_list->addColumn($column_id_casamento);
        // $this->detail_list->addColumn($column_id_usuario);
        $this->detail_list->addColumn($column_edit);
        $this->detail_list->addColumn($column_del);
        $this->detail_list->addColumn($column_tipo_fornecedor);
        $this->detail_list->addColumn($column_nome_fornecedor);
        $this->detail_list->addColumn($column_valor);
        // $this->detail_list->addColumn($column_obs);




        //
        // // create EDIT action
        // $action_edit = new TDataGridAction(array($this, 'onEditDetail'));
        // //$action_edit->setUseButton(TRUE);
        // //$action_edit->setButtonClass('btn btn-default');
        // $action_edit->setLabel(_t('Edit'));
        // $action_edit->setImage('fa:pencil-square-o blue fa-lg');
        // $action_edit->setField('id');
        // $this->detail_list->addAction($action_edit);
        //
        // // create DELETE action
        // $action_del = new TDataGridAction(array($this, 'onDeleteDetail'));
        // //$action_del->setUseButton(TRUE);
        // //$action_del->setButtonClass('btn btn-default');
        // $action_del->setLabel(_t('Delete'));
        // $action_del->setImage('fa:trash-o red fa-lg');
        // $action_del->setField('id');
        // $this->detail_list->addAction($action_del);








        $column_valor->setTransformer( function($value, $object, $row) {
            return 'R$ ' . number_format($value, 2, ',', '.');
            // return 'R$ ' . $value;
        });

        //create the datagrid model
        $this->detail_list->createModel();
        // creates a Datagrid
        // $this->detail_list = new TQuickGrid;
        // $this->detail_list = new BootstrapDatagridWrapper($this->detail_list);
        // $this->detail_list->setHeight( 175 );
        // $this->detail_list->style = 'width:100%';
        // $this->detail_list->makeScrollable();
        // $this->detail_list->disableDefaultClick();
        // $this->detail_list->addQuickColumn('Edit', 'edit', 'left', 10);
        // $this->detail_list->addQuickColumn('Del', 'delete', 'left', 10);
        //
        // // items
        // $this->detail_list->addQuickColumn('Tipo Fornecedor', 'tipo_fornecedor', 'left', '40%');
        // $this->detail_list->addQuickColumn('Nome Fornecedor', 'nome_fornecedor', 'left', '40%');
        // $this->detail_list->addQuickColumn('Valor', 'valor', 'left', '20%');
        // $this->detail_list->addQuickColumn('Obs', 'obs', 'left', '100%');

        $column_valor->setTotalFunction( function($values) {
            return array_sum((array) $values);
        });

        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };

        $column_valor->setTransformer($format_value);

        $this->detail_list->createModel();

        $row = $table_detail->addRow();
        $row->addCell($this->detail_list);

        // create an action button (save)
        $save_button=new TButton('save');
        $save_button->setAction(new TAction(array($this, 'onSave')), _t('Save'));
        $save_button->class = 'btn btn-sm btn-primary';
        $save_button->setImage('fa:floppy-o');

        // create an new button (edit with no parameters)
        $new_button  = TButton::create('search',  array('CasamentoSeek', 'onReload'), _t('Search'),  'fa:search red');
        $list_button = TButton::create('list',  array('ContratosList', 'onReload'), _t('List'),  'fa:list blue blue');



        // Change Action
        $detail_tipo_fornecedor->setChangeAction( new TAction( array($this, 'onStateChange' )) );



        // define form fields
        $this->formFields   = array($id,$noiva,$noivo,$orcamento,$detail_tipo_fornecedor,$detail_nome_fornecedor,$detail_valor,$detail_obs);
        $this->formFields[] = $btn_save_detail;
        $this->formFields[] = $save_button;
        $this->formFields[] = $new_button;
        $this->formFields[] = $list_button;
        $this->formFields[] = $detail_id;
        $this->form->setFields( $this->formFields );

        $l1 = new TLabel('Não esqueça de clicar em SALVAR!');

        $table_master->addRowSet( array($save_button, $new_button, $list_button, $l1), '', '')->class = 'tformaction'; // CSS class

        // create the page container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }


    /**
     * Clear form
     * @param $param URL parameters
     */
    public function onClear($param)
    {
        $this->form->clear(TRUE);
        TSession::setValue(__CLASS__.'_items', array());
        $this->onReload( $param );
    }

    /**
     * Save an item from form to session list
     * @param $param URL parameters
     */
    public function onSaveDetail( $param )
    {
        try
        {
            TTransaction::open('organizamc');
            $data = $this->form->getData();

            /** validation sample
            if (! $data->fieldX)
                throw new Exception('The field fieldX is required');
            **/

            $items = TSession::getValue(__CLASS__.'_items');
            $key = empty($data->detail_id) ? 'X'.mt_rand(1000000000, 1999999999) : $data->detail_id;

            $items[ $key ] = array();
            $items[ $key ]['id'] = $key;
            $items[ $key ]['tipo_fornecedor'] = $data->detail_tipo_fornecedor;
            $items[ $key ]['nome_fornecedor'] = $data->detail_nome_fornecedor;
            $items[ $key ]['valor'] = $data->detail_valor;
            $items[ $key ]['obs'] = $data->detail_obs;

            TSession::setValue(__CLASS__.'_items', $items);

            // clear detail form fields
            $data->detail_id = '';
            $data->detail_tipo_fornecedor = '';
            $data->detail_nome_fornecedor = '';
            $data->detail_valor = '';
            $data->detail_obs = '';

            TTransaction::close();
            $this->form->setData($data);

            $this->onReload( $param ); // reload the items
        }
        catch (Exception $e)
        {
            $this->form->setData( $this->form->getData());
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * Load an item from session list to detail form
     * @param $param URL parameters
     */
    public function onEditDetail( $param )
    {
        $data = $this->form->getData();

        // read session items
        $items = TSession::getValue(__CLASS__.'_items');

        // get the session item
        $item = $items[ $param['item_key'] ];

        $data->detail_id = $item['id'];
        $data->detail_tipo_fornecedor = $item['tipo_fornecedor'];
        $data->detail_nome_fornecedor = $item['nome_fornecedor'];
        $data->detail_valor = $item['valor'];
        $data->detail_obs = $item['obs'];

        // fill detail fields
        $this->form->setData( $data );

        $this->onReload( $param );
    }

    /**
     * Delete an item from session list
     * @param $param URL parameters
     */
    public function onDeleteDetail( $param )
    {
        $data = $this->form->getData();

        // reset items
            $data->detail_tipo_fornecedor = '';
            $data->detail_nome_fornecedor = '';
            $data->detail_valor = '';
            $data->detail_obs = '';

        // clear form data
        $this->form->setData( $data );

        // read session items
        $items = TSession::getValue(__CLASS__.'_items');

        // delete the item from session
        unset($items[ $param['item_key'] ] );
        TSession::setValue(__CLASS__.'_items', $items);

        // reload items
        $this->onReload( $param );
    }

    /**
     * Load the items list from session
     * @param $param URL parameters
     */
    public function onReload($param)
    {
        // read session items
        $items = TSession::getValue(__CLASS__.'_items');

        $this->detail_list->clear(); // clear detail list
        $data = $this->form->getData();

        if ($items)
        {
            $cont = 1;
            foreach ($items as $list_item_key => $list_item)
            {
                $item_name = 'prod_' . $cont++;
                $item = new StdClass;

                // create action buttons
                $action_del = new TAction(array($this, 'onDeleteDetail'));
                $action_del->setParameter('item_key', $list_item_key);

                $action_edi = new TAction(array($this, 'onEditDetail'));
                $action_edi->setParameter('item_key', $list_item_key);

                $button_del = new TButton('delete_detail'.$cont);
                $button_del->class = 'btn btn-default btn-sm';
                $button_del->setAction( $action_del, '' );
                $button_del->setImage('fa:trash-o red fa-lg');

                $button_edi = new TButton('edit_detail'.$cont);
                $button_edi->class = 'btn btn-default btn-sm';
                $button_edi->setAction( $action_edi, '' );
                $button_edi->setImage('fa:edit blue fa-lg');

                $item->edit   = $button_edi;
                $item->delete = $button_del;

                $this->formFields[ $item_name.'_edit' ] = $item->edit;
                $this->formFields[ $item_name.'_delete' ] = $item->delete;

                // items
                $item->id = $list_item['id'];
                $item->tipo_fornecedor = $list_item['tipo_fornecedor'];
                $item->nome_fornecedor = $list_item['nome_fornecedor'];
                $item->valor = $list_item['valor'];
                $item->obs = $list_item['obs'];

                $row = $this->detail_list->addItem( $item );
                $row->onmouseover='';
                $row->onmouseout='';
            }

            $this->form->setFields( $this->formFields );
        }

        $this->loaded = TRUE;
    }

    /**
     * Load Master/Detail data from database to form/session
     */
    public function onEdit($param)
    {
        try
        {
            TTransaction::open('organizamc');

            if (isset($param['key']))
            {
                $key = $param['key'];
                $pegaCas = new Contratos($key);
                $items  = Contratos::where('id_casamento', '=', $pegaCas->id_casamento)->load();
                $object = new Casamento($pegaCas->id_casamento);

                $session_items = array();
                foreach( $items as $item )
                {
                    $item_key = $item->id;
                    $session_items[$item_key] = $item->toArray();
                    $session_items[$item_key]['id'] = $item->id;
                    $session_items[$item_key]['tipo_fornecedor'] = $item->tipo_fornecedor_descricao;
                    $session_items[$item_key]['nome_fornecedor'] = $item->nome_fornecedor_nome;
                    $session_items[$item_key]['valor'] = $item->valor;
                    $session_items[$item_key]['obs'] = $item->obs;
                }
                TSession::setValue(__CLASS__.'_items', $session_items);

                $this->form->setData($object); // fill the form with the active record data
                $this->onReload( $param ); // reload items list
                TTransaction::close(); // close transaction
            }
            else
            {
                $this->form->clear(TRUE);
                TSession::setValue(__CLASS__.'_items', null);
                $this->onReload( $param );
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    /**
     * Save the Master/Detail data from form/session to database
     */
    public function onSave()
    {
        try
        {
            // open a transaction with database
            TTransaction::open('organizamc');

            $data = $this->form->getData();
            $master = new Casamento;
            $master->fromArray( (array) $data);
            $this->form->validate(); // form validation

            $master->store(); // save master object
            // delete details
            $old_items = Contratos::where('id_casamento', '=', $master->id)->load();

            $keep_items = array();

            // get session items
            $items = TSession::getValue(__CLASS__.'_items');

            if( $items )
            {
                foreach( $items as $item )
                {
                    if (substr($item['id'],0,1) == 'X' ) // new record
                    {
                        $detail = new Contratos;
                    }
                    else
                    {
                        $detail = Contratos::find($item['id']);
                    }
                    $detail->tipo_fornecedor  = $item['tipo_fornecedor_descricao'];
                    $detail->nome_fornecedor  = $item['nome_fornecedor_nome'];
                    $detail->valor  = $item['valor'];
                    $detail->obs  = $item['obs'];
                    $detail->id_casamento = $master->id;
                    $detail->id_usuario   = TSession::getValue('userid');
                    $detail->store();

                    $keep_items[] = $detail->id;
                }
            }

            if ($old_items)
            {
                foreach ($old_items as $old_item)
                {
                    if (!in_array( $old_item->id, $keep_items))
                    {
                        $old_item->delete();
                    }
                }
            }
            TTransaction::close(); // close the transaction

            // reload form and session items
            $this->onEdit(array('key'=>$master->id));

            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback();
        }
    }

    /**
     * Show the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }


    /**
     * Fire form events
     * @param $param Request
     */
    public function fireEvents( $object )
    {
        $obj = new stdClass;
        $obj->detail_tipo_fornecedor = $object->detail_tipo_fornecedor;
        $obj->detail_nome_fornecedor = $object->detail_nome_fornecedor;
        TForm::sendData('form_Contrato', $obj);
    }


    /**
     * Action to be executed when the user changes the state
     * @param $param Action parameters
     */
    public static function onStateChange($param)
    {
        try
        {
            TTransaction::open('organizamc');
            if ($param['detail_tipo_fornecedor'])
            {
                $criteria = TCriteria::create( ['tipo' => $param['detail_tipo_fornecedor'] ] );

                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('form_Contrato', 'detail_nome_fornecedor', 'organizamc', 'Fornecedor', 'id', 'nome', 'nome', $criteria, TRUE);
            }
            // else
            // {
            //     TCombo::clearField('form_Fornecedor', 'municipio');
            // }

            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}
