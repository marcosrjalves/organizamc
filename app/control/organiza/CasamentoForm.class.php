<?php
/**
 * CasamentoForm Form
 * @author  Marcos Rodrigo Jung Alves
 */
class CasamentoForm extends TPage
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
        $this->form = new BootstrapFormBuilder;
        $this->form->class = 'tform'; // change CSS class
        // $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style

        // define the form title
        $this->form->setFormTitle('CADASTRO DE EVENTO');

        $filterLocal = new TCriteria;
        $filterLocal->add(new TFilter('ativo', 'like', 'S'));


        // create the form fields
        $id = new TEntry('id');
        // $id_usuario = new TCombo('id_usuario');
        $id_usuario = new TDBCombo('id_usuario', 'permission', 'SystemUser', 'id', 'name');
        $noiva = new TEntry('noiva');
        $noivo = new TEntry('noivo');
        $dia = new TDate('dia');
        $horas = new TSpinner('horas');
        $minutos = new TSpinner('minutos');
        $orcamento = new TEntry('orcamento');
        $local_cerimonia = new TDBUniqueSearch('local_cerimonia', 'organizamc', 'Locais', 'id', 'nome', 'nome', $filterLocal);
        $local_recepcao =  new TDBUniqueSearch('local_recepcao', 'organizamc', 'Locais', 'id', 'nome', 'nome', $filterLocal);
        $obs = new TText('obs');
        $l1 = new TLabel('ID:');
        $l2 = new TLabel('Usuário:');
        $l3 = new TLabel('Noiva:');
        $l4 = new TLabel('Noivo:');
        $l5 = new TLabel('Data:');
        $l6 = new TLabel('Horário:');
        $l7 = new TLabel('Orçamento:');
        $l8 = new TLabel('Local da Cerimônia:');
        $l9 = new TLabel('Local da Recepção:');
        $l10 = new TLabel('Observações:');
        $horas->setSize('100%');
        $minutos->setSize('100%');


        // add the fields
        $this->form->addFields([$l1], [$id]);
        $this->form->addFields([$l2], [$id_usuario]);
        $this->form->addFields([$l3], [$noiva]);
        $this->form->addFields([$l4], [$noivo]);
        $this->form->addFields([$l5], [$dia]);
        $this->form->addFields([$l6], [$horas],[$minutos]);
        $this->form->addFields([$l7], [$orcamento]);
        $this->form->addFields([$l8], [$local_cerimonia]);
        $this->form->addFields([$l9], [$local_recepcao]);
        $this->form->addFields([$l10], [$obs]);




        $l1->setFontStyle('bold');
        $l2->setFontStyle('bold');
        $l3->setFontStyle('bold');

        $obs->setSize('100%', 200);

        $dia->setMask('dd/mm/yyyy');
        $dia->setDatabaseMask('yyyy-mm-dd');
        $orcamento->setNumericMask(2, ',','.','.');
        $horas->setRange(0,23,1);
        $minutos->setRange(0,60,1);




        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }

        /** samples
         $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( 100, 40 ); // set size
         **/

        $salvar = new TButton('salvar');
        $salvar = $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:save white');
        $salvar->class = 'btn btn-primary';

        $new = new TButton('novo');
        $new = $this->form->addAction(_t('New'), new TAction(array($this, 'onClear')), 'bs:plus-sign green');

        $list = new TButton('lista');
        $list = $this->form->addAction(_t('List'), new TAction(array('CasamentoList', 'onReload')), 'bs:list blue blue');




        // $this->fom->addFields([$salvar]);
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);



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

            $object = new Casamento;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            $concathora = $param['horas'] . ':' . $param['minutos'];
            $object->hora = $concathora;
            $object->id_usuario = TSession::getValue('userid');
            $object->noiva = strtoupper($object->noiva);
            $object->noivo = strtoupper($object->noivo);
            $object->store(); // save the object

            // get the generated id
            $data->id = $object->id;

            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction

            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved') . $dias);
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
                $object = new Casamento($key); // instantiates the Active Record
                $exphoras = explode(':',$object->hora);
                $arraycas = $object;
                $arraycas->horas = $exphoras[0];
                $arraycas->minutos = $exphoras[1];
                $this->form->setData($arraycas); // fill the form
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

}
