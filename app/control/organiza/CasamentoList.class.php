<?php
/**
 * CasamentoList Listing
 * @author  Marcos Rodrigo Jung Alves
 */
class CasamentoList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;

    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();

        // creates the form
        $this->form = new TQuickForm('form_search_Casamento');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style
        $this->form->setFormTitle('Casamento');


        // create the form fields
        $noiva = new TEntry('noiva');
        $noivo = new TEntry('noivo');
        $dia = new TEntry('dia');
        $hora = new TEntry('hora');
        $orcamento = new TEntry('orcamento');
        $local_cerimonia = new TDBUniqueSearch('local_cerimonia', 'organizamc', 'Locais', 'id', 'nome');
        $local_recepcao = new TDBUniqueSearch('local_recepcao', 'organizamc', 'Locais', 'id', 'nome');


        // add the fields
        $this->form->addQuickField('Noiva', $noiva,  '100%' );
        $this->form->addQuickField('Noivo', $noivo,  '100%' );
        $this->form->addQuickField('Dia', $dia,  '100%' );
        $this->form->addQuickField('Hora', $hora,  '100%' );
        $this->form->addQuickField('Orcamento', $orcamento,  '100%' );
        $this->form->addQuickField('Local Cerimonia', $local_cerimonia,  '100%' );
        $this->form->addQuickField('Local Recepcao', $local_recepcao,  '100%' );


        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Casamento_filter_data') );

        // add the search form actions
        $btn = $this->form->addQuickAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addQuickAction(_t('New'),  new TAction(array('CasamentoForm', 'onEdit')), 'bs:plus-sign green');

        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');


        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_id_usuario = new TDataGridColumn('user_nome->name', 'Usuario', 'right');
        $column_noiva = new TDataGridColumn('noiva', 'Noiva', 'left');
        $column_noivo = new TDataGridColumn('noivo', 'Noivo', 'left');
        $column_dia = new TDataGridColumn('dia', 'Dia', 'left');
        $column_hora = new TDataGridColumn('hora', 'Hora', 'left');
        $column_orcamento = new TDataGridColumn('orcamento', 'Orcamento', 'right');
        $column_local_cerimonia = new TDataGridColumn('local_cerimonia_nome->nome', 'Cerimonia', 'right');
        $column_local_recepcao = new TDataGridColumn('local_recepcao_nome->nome', 'Recepcao', 'right');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_id_usuario);
        $this->datagrid->addColumn($column_noiva);
        $this->datagrid->addColumn($column_noivo);
        $this->datagrid->addColumn($column_dia);
        $this->datagrid->addColumn($column_hora);
        $this->datagrid->addColumn($column_orcamento);
        $this->datagrid->addColumn($column_local_cerimonia);
        $this->datagrid->addColumn($column_local_recepcao);


        // create EDIT action
        $action_edit = new TDataGridAction(array('CasamentoForm', 'onEdit'));
        //$action_edit->setUseButton(TRUE);
        //$action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('fa:pencil-square-o blue fa-lg');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);

        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        //$action_del->setUseButton(TRUE);
        //$action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('fa:trash-o red fa-lg');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);

        // create the datagrid model
        $this->datagrid->createModel();

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());



        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add(TPanelGroup::pack('LISTAGEM DE CASAMENTOS', $this->form));
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));

        parent::add($container);
    }

    /**
     * Inline record editing
     * @param $param Array containing:
     *              key: object ID value
     *              field name: object attribute to be updated
     *              value: new attribute content
     */
    public function onInlineEdit($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];

            TTransaction::open('organizamc'); // open a transaction with database
            $object = new Casamento($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction

            $this->onReload($param); // reload the listing
            new TMessage('info', "Record Updated");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();

        // clear session filters
        TSession::setValue('CasamentoList_filter_noiva',   NULL);
        TSession::setValue('CasamentoList_filter_noivo',   NULL);
        TSession::setValue('CasamentoList_filter_dia',   NULL);
        TSession::setValue('CasamentoList_filter_hora',   NULL);
        TSession::setValue('CasamentoList_filter_orcamento',   NULL);
        TSession::setValue('CasamentoList_filter_local_cerimonia',   NULL);
        TSession::setValue('CasamentoList_filter_local_recepcao',   NULL);

        if (isset($data->noiva) AND ($data->noiva)) {
            $filter = new TFilter('noiva', 'like', "%{$data->noiva}%"); // create the filter
            TSession::setValue('CasamentoList_filter_noiva',   $filter); // stores the filter in the session
        }


        if (isset($data->noivo) AND ($data->noivo)) {
            $filter = new TFilter('noivo', 'like', "%{$data->noivo}%"); // create the filter
            TSession::setValue('CasamentoList_filter_noivo',   $filter); // stores the filter in the session
        }


        if (isset($data->dia) AND ($data->dia)) {
            $filter = new TFilter('dia', 'like', "%{$data->dia}%"); // create the filter
            TSession::setValue('CasamentoList_filter_dia',   $filter); // stores the filter in the session
        }


        if (isset($data->hora) AND ($data->hora)) {
            $filter = new TFilter('hora', 'like', "%{$data->hora}%"); // create the filter
            TSession::setValue('CasamentoList_filter_hora',   $filter); // stores the filter in the session
        }


        if (isset($data->orcamento) AND ($data->orcamento)) {
            $filter = new TFilter('orcamento', 'like', "%{$data->orcamento}%"); // create the filter
            TSession::setValue('CasamentoList_filter_orcamento',   $filter); // stores the filter in the session
        }


        if (isset($data->local_cerimonia) AND ($data->local_cerimonia)) {
            $filter = new TFilter('local_cerimonia', 'like', "%{$data->local_cerimonia}%"); // create the filter
            TSession::setValue('CasamentoList_filter_local_cerimonia',   $filter); // stores the filter in the session
        }


        if (isset($data->local_recepcao) AND ($data->local_recepcao)) {
            $filter = new TFilter('local_recepcao', 'like', "%{$data->local_recepcao}%"); // create the filter
            TSession::setValue('CasamentoList_filter_local_recepcao',   $filter); // stores the filter in the session
        }


        // fill the form with data again
        $this->form->setData($data);

        // keep the search data in the session
        TSession::setValue('Casamento_filter_data', $data);

        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }

    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'organizamc'
            TTransaction::open('organizamc');

            // creates a repository for Casamento
            $repository = new TRepository('Casamento');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;

            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);


            if (TSession::getValue('CasamentoList_filter_noiva')) {
                $criteria->add(TSession::getValue('CasamentoList_filter_noiva')); // add the session filter
            }


            if (TSession::getValue('CasamentoList_filter_noivo')) {
                $criteria->add(TSession::getValue('CasamentoList_filter_noivo')); // add the session filter
            }


            if (TSession::getValue('CasamentoList_filter_dia')) {
                $criteria->add(TSession::getValue('CasamentoList_filter_dia')); // add the session filter
            }


            if (TSession::getValue('CasamentoList_filter_hora')) {
                $criteria->add(TSession::getValue('CasamentoList_filter_hora')); // add the session filter
            }


            if (TSession::getValue('CasamentoList_filter_orcamento')) {
                $criteria->add(TSession::getValue('CasamentoList_filter_orcamento')); // add the session filter
            }


            if (TSession::getValue('CasamentoList_filter_local_cerimonia')) {
                $criteria->add(TSession::getValue('CasamentoList_filter_local_cerimonia')); // add the session filter
            }


            if (TSession::getValue('CasamentoList_filter_local_recepcao')) {
                $criteria->add(TSession::getValue('CasamentoList_filter_local_recepcao')); // add the session filter
            }


            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);

            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }

            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }

            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);

            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit

            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * Ask before deletion
     */
    public function onDelete($param)
    {
        // define the delete action
        $action = new TAction(array($this, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead

        // shows a dialog to the user
        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }

    /**
     * Delete a record
     */
    public function Delete($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            TTransaction::open('organizamc'); // open a transaction with database
            $object = new Casamento($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            $this->onReload( $param ); // reload the listing
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted')); // success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }





    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
}
