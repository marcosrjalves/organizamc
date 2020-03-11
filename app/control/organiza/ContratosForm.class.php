<?php
/**
 * ContratosForm Form
 * @author  Marcos Rodrigo Jung Alves
 */
class ContratosForm extends TPage
{
    protected $form; // form

    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();

        // creates the form
        $this->form = new TQuickForm('form_Contratos');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style

        // define the form title
        $this->form->setFormTitle('Contratos');

        $filterFornecedor = new TCriteria;
        $filterFornecedor->add(new TFilter('ativo', 'like', 'S'));


        // create the form fields
        $tipo_fornecedor = new TDBCombo('tipo_fornecedor', 'organizamc', 'TipoFornecedor', 'id', 'descricao', 'descricao');
        $nome_fornecedor = new TDBCombo('nome_fornecedor', 'organizamc', 'Fornecedor', 'id', 'nome', 'nome', $filterFornecedor);
        $valor = new TEntry('valor');
        $obs = new TEntry('obs');


        // add the fields
        $this->form->addQuickField('Tipo Fornecedor', $tipo_fornecedor,  '50%' , new TRequiredValidator);
        $this->form->addQuickField('Nome Fornecedor', $nome_fornecedor,  '50%' , new TRequiredValidator);
        $this->form->addQuickField('Valor', $valor,  '50%' , new TRequiredValidator);
        $this->form->addQuickField('Obs', $obs,  '100%' );


        $tipo_fornecedor->setChangeAction( new TAction( array($this, 'onStateChange' )) );


        // $format_value = function($value) {
        //     if (is_numeric($value)) {
        //         return 'R$ '.number_format($value, 2, ',', '.');
        //     }
        //     return $value;
        // };
        //
        // $valor->setTransformer($format_value);




        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }

        /** samples
         $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( 100, 40 ); // set size
         **/

        // create the form actions
        $btn = $this->form->addQuickAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addQuickAction(_t('New'),  new TAction(array($this, 'onClear')), 'bs:plus-sign green');
        $this->form->addQuickAction(_t('List'),  new TAction(array('ContratosList', 'onReload')), 'bs:list blue blue');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add(TPanelGroup::pack('CADASTRO DE NOVO CONTRATO', $this->form));

        parent::add($container);
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('organizamc'); // open a transaction

            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/

            $this->form->validate(); // validate form data

            $object = new Contratos;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            $object->id_usuario = TSession::getValue('userid');
            $criteria = new TCriteria;
            $repository = new TRepository('Casamento');
            $criteria->add(new TFilter('id_usuario', '=', $object->id_usuario));
            $casamento = $repository->load($criteria);
            $object->id_casamento = $casamento[0]->id;
            $object->store(); // save the object

            // get the generated id
            $data->id = $object->id;

            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction

            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }

    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }

    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('organizamc'); // open a transaction
                $object = new Contratos($key); // instantiates the Active Record
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    /**
     * Fire form events
     * @param $param Request
     */
    public function fireEvents( $object )
    {
        $obj = new stdClass;
        $obj->tipo_fornecedor = $object->tipo_fornecedor;
        $obj->nome_fornecedor = $object->nome_fornecedor;
        TForm::sendData('form_Contratos', $obj);
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
            if ($param['tipo_fornecedor'])
            {
                $criteria = TCriteria::create( ['tipo' => $param['tipo_fornecedor'] ] );

                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('form_Contratos', 'nome_fornecedor', 'organizamc', 'Fornecedor', 'id', 'nome', 'nome', $criteria, TRUE);
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
