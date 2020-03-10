<?php
/**
 * LocaisForm Form
 * @author  Marcos Rodrigo Jung Alves
 */
class LocaisFormForUsers extends TPage
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
        $this->form = new TQuickForm('form_Locais');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style

        // define the form title
        $this->form->setFormTitle('Locais');



        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $rua = new TEntry('rua');
        $bairro = new TEntry('bairro');
        $municipio = new TDBCombo('municipio', 'organizamc', 'Cidades', 'id', 'nome', 'nome');
        $estado = new TDBCombo('estado', 'organizamc', 'Estados', 'id', 'uf', 'uf');
        $numero = new TEntry('numero');
        $ativo = new THidden('ativo');
        $aprovado = new THidden('Aprovado');

        $options = array();
        $options = ['S' => 'Sim', 'N' => 'Não'];


        $ativo->addItems($options);

        // add the fields
        $this->form->addQuickField('Id', $id,  '50%' );
        $this->form->addQuickField('Nome', $nome,  '100%' , new TRequiredValidator);
        $this->form->addQuickField('Estado', $estado,  '100%' , new TRequiredValidator);
        $this->form->addQuickField('Municipio', $municipio,  '100%' , new TRequiredValidator);
        $this->form->addQuickField('Rua', $rua,  '100%' , new TRequiredValidator);
        $this->form->addQuickField('Numero', $numero,  '100%' );
        $this->form->addQuickField('Bairro', $bairro,  '100%' , new TRequiredValidator);
        $this->form->addQuickField('Ativo', $ativo,  '100%');
        $this->form->addQuickField('Aprovado', $aprovado,  '100%');



        $estado->setChangeAction( new TAction( array($this, 'onStateChange' )) );





        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }

        $ativo->setEditable(FALSE);

        /** samples
         $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( 100, 40 ); // set size
         **/

        // create the form actions
        $btn = $this->form->addQuickAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addQuickAction(_t('New'),  new TAction(array($this, 'onClear')), 'bs:plus-sign green');
        $this->form->addQuickAction(_t('List'),  new TAction(array('LocaisListForUsers', 'onReload')), 'bs:list blue blue');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add(TPanelGroup::pack('CADASTRO DE LOCAIS', $this->form));

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


            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file


            $this->form->validate(); // validate form data

            $object = new Locais;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            $object->nome =    strtoupper($object->nome);
            $object->rua =     strtoupper($object->rua);
            $object->bairro =  strtoupper($object->bairro);
            $object->numero =  strtoupper($object->numero);
            if ($object->ativo != 'N')
            {
                  $object->ativo  =  'S';
            }
            if ($object->aprovado != 'S')
            {
                  $object->aprovado  =  'N';
            }
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
                $object = new Locais($key); // instantiates the Active Record
                $this->form->setData($object); // fill the form


                if ($object->aprovado == 'S')
                {
                    TEntry::disableField('form_Locais', 'nome');
                    TEntry::disableField('form_Locais', 'rua');
                    TEntry::disableField('form_Locais', 'numero');
                    TEntry::disableField('form_Locais', 'bairro');
                    TDBCombo::disableField('form_Locais', 'municipio');
                    TDBCombo::disableField('form_Locais', 'estado');
                }

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
        $obj->estado = $object->estado;
        $obj->municipio = $object->municipio;
        TForm::sendData('form_Locais', $obj);
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
            if ($param['estado'])
            {
                $criteria = TCriteria::create( ['uf' => $param['estado'] ] );

                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('form_Locais', 'municipio', 'organizamc', 'Cidades', 'id', 'nome', 'nome', $criteria, TRUE);
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
