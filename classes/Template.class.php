<?php

/* Models and managers used by the Template model */
require_once 'Model.class.php';
require_once 'TemplateManager.class.php';

class Template extends Model {

    const TYPE_EMAIL = 'email';
    const TYPE_SMS = 'sms';
    const TYPE_TEXT = 'text';
    const NEWSLETTER_TEMPLATE_ID = 25;

    // const templateId_account_bevestingen = 1; <- @example of defining an specific template id

    public $templateId;
    public $description; // description of the template, where is it used for
    public $name; // unqiue name for template
    public $templateGroupId; // id of the group the templates belongs to
    public $type = null; // type of the template (email,sms,text)
    public $subject; // subject is used for email templates
    public $template; // the actual body/sms message/text template
    private $deletable = 1;
    private $editable = 1;
    public $created; //created timestamp
    public $modified; //last modified timestamp
    private $oTemplateGroup; // references TemplateGroup class

    /**
     * validate object 
     */

    public function validate() {
        if (empty($this->description))
            $this->setPropInvalid('description');
        if (empty($this->type))
            $this->setPropInvalid('type');
        if (empty($this->templateGroupId))
            $this->setPropInvalid('templateGroupId');
        if (!empty($this->name) && ($oTemplate = TemplateManager::getTemplateByName($this->name))) {
            if ($oTemplate->templateId != $this->templateId) {
                $this->setPropInvalid('name');
            }
        }
    }

    /**
     * check if template is deletable
     * @return boolean
     */
    public function isDeletable() {
        return $this->deletable;
    }

    /**
     * check if template is editable
     * @return boolean
     */
    public function isEditable() {
        return $this->editable;
    }

    /**
     * get template group
     * @return TemplateGroup
     */
    public function getTemplateGroup() {
        if ($this->oTemplateGroup === null)
            $this->oTemplateGroup = TemplateManager::getTemplateGroupById($this->templateGroupId);
        return $this->oTemplateGroup;
    }

    /**
     * return subject
     * @return string
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * return template
     * @return string
     */
    public function getTemplate() {
        return $this->template;
    }

    /**
     * replace template variables with real values
     * @param mixed $oObject
     * @param array $aExtraValues
     * @param boolean $bTest
     */
    public function replaceVariables($oObject, array $aExtraValues = array(), $bTest = false) {

        $aKeys = array();
        $aValues = array();

        if ($oObject instanceof Order) {
            $oOrder = $oObject;
            $sStatus = $oOrder->getStatus()->getLabel();
            $fSubTotalPrice = $oOrder->getSubtotalProducts(true);
            $sDeliveryMethod = $oOrder->getDeliveryMethod()->name;
            $fDeliveryPrice = $oOrder->getDeliveryPrice(true);
            $sPaymentMethod = $oOrder->getPaymentMethod()->name;
            $fPaymentPrice = $oOrder->getPaymentPrice();
            $fTotalPriceWithoutTax = $oOrder->getTotal(false);
            $fTotalPriceWithTax = $oOrder->getTotal(true);
            $fTotalTaxPrice = $oOrder->getBTW();
            $sCustomerGoogleMapsLink = '<a href="https://maps.google.com/maps?q=' . urlencode($oOrder->delivery_lat . ', ' . $oOrder->delivery_lng) . '&hl=nl" target="_blank">Klik hier om de locatie te bekijken in Google Maps</a>';
        } elseif ($oObject instanceof Customer) {
            $oCustomer = $oObject;
        } elseif ($bTest) {
            $oOrder = new Order();
            $oOrder->orderId = 12345;
            $oOrder->email = 'test@persoon.nl';
            $oOrder->invoice_gender = 'M';
            $oOrder->invoice_firstName = 'Test';
            $oOrder->invoice_insertion = 'van den';
            $oOrder->invoice_lastName = 'Persoon';
            $oOrder->invoice_companyName = 'Testbedrijf';
            $oOrder->invoice_address = 'Teststraat 1';
            $oOrder->invoice_postalCode = '0000 XX';
            $oOrder->invoice_city = 'Teststad';
            $oOrder->invoice_phone = '0123456789';
            $oOrder->delivery_gender = 'M';
            $oOrder->delivery_firstName = 'Test';
            $oOrder->delivery_insertion = 'van den';
            $oOrder->delivery_lastName = 'Persoon';
            $oOrder->delivery_companyName = 'Testbedrijf';
            $oOrder->delivery_address = 'Teststraat 1';
            $oOrder->delivery_postalCode = '0000 XX';
            $oOrder->delivery_city = 'Teststad';
            $sStatus = 'Wachten op betaling';
            $fSubTotalPrice = 150;
            $sDeliveryMethod = 'Bezorgen';
            $fDeliveryPrice = 2.50;
            $sPaymentMethod = 'iDEAL';
            $fPaymentPrice = 2.50;
            $fTotalPriceWithoutTax = 132.2314;
            $fTotalPriceWithTax = TaxManager::addTax($fTotalPriceWithoutTax, TaxManager::getPercentageById(TaxManager::taxPercentageId_high), $fTotalTaxPrice);
            $sCustomerGoogleMapsLink = '<a href="https://maps.google.com/maps?q=52.206615%2C+4.871148&hl=nl" target="_blank">Klik hier om de locatie te bekijken in Google Maps</a>';

            $oCustomer = new Customer();
            $oCustomer->firstName = 'Test';
            $oCustomer->insertion = 'vd';
            $oCustomer->lastName = 'Persoon';
            $oCustomer->confirmCode = '123test123';
            $oCustomer->email = 'test@persoon.nl';
        }

        # replace Order variables with the objects values
        if (!empty($oOrder)) {
            $aKeys[] = '[order_orderId]';
            $aValues[] = $oOrder->orderId;

            $aKeys[] = '[order_status]';
            $aValues[] = $sStatus;

            $aKeys[] = '[order_email]';
            $aValues[] = _e($oOrder->email);

            $aKeys[] = '[order_invoice_companyName]';
            $aValues[] = _e($oOrder->invoice_companyName);

            $aKeys[] = '[order_invoice_gender]';
            $aValues[] = _e($oOrder->invoice_gender);

            $aKeys[] = '[order_invoice_firstName]';
            $aValues[] = _e($oOrder->invoice_firstName);

            $aKeys[] = '[order_invoice_insertion]';
            $aValues[] = _e($oOrder->invoice_insertion);

            $aKeys[] = '[order_invoice_lastName]';
            $aValues[] = _e($oOrder->invoice_lastName);

            $aKeys[] = '[order_invoice_fullName]';
            $aValues[] = _e($oOrder->invoice_firstName . ($oOrder->invoice_insertion ? ' ' . $oOrder->invoice_insertion : '') . ($oOrder->invoice_lastName ? ' ' . $oOrder->invoice_lastName : ''));

            $aKeys[] = '[order_invoice_address]';
            $aValues[] = _e($oOrder->invoice_address);

            $aKeys[] = '[order_invoice_houseNumber]';
            $aValues[] = _e($oOrder->invoice_houseNumber);

            $aKeys[] = '[order_invoice_houseNumberAddition]';
            $aValues[] = _e($oOrder->invoice_houseNumberAddition);

            $aKeys[] = '[order_invoice_fullAddress]';
            $aValues[] = _e($oOrder->invoice_address . ($oOrder->invoice_houseNumber ? ' ' . $oOrder->invoice_houseNumber : '') . ($oOrder->invoice_houseNumberAddition ? ' ' . $oOrder->invoice_houseNumberAddition : ''));

            $aKeys[] = '[order_invoice_postalCode]';
            $aValues[] = _e($oOrder->invoice_postalCode);

            $aKeys[] = '[order_invoice_city]';
            $aValues[] = _e($oOrder->invoice_city);

            $aKeys[] = '[order_invoice_phone]';
            $aValues[] = _e($oOrder->invoice_phone);

            $aKeys[] = '[order_delivery_companyName]';
            $aValues[] = _e($oOrder->delivery_companyName);

            $aKeys[] = '[order_delivery_gender]';
            $aValues[] = _e($oOrder->delivery_gender);

            $aKeys[] = '[order_delivery_firstName]';
            $aValues[] = _e($oOrder->delivery_firstName);

            $aKeys[] = '[order_delivery_insertion]';
            $aValues[] = _e($oOrder->delivery_insertion);

            $aKeys[] = '[order_delivery_lastName]';
            $aValues[] = _e($oOrder->delivery_lastName);

            $aKeys[] = '[order_delivery_fullName]';
            $aValues[] = _e($oOrder->delivery_firstName . ($oOrder->delivery_insertion ? ' ' . $oOrder->delivery_insertion : '') . ($oOrder->delivery_lastName ? ' ' . $oOrder->delivery_lastName : ''));

            $aKeys[] = '[order_delivery_address]';
            $aValues[] = _e($oOrder->delivery_address);

            $aKeys[] = '[order_delivery_houseNumber]';
            $aValues[] = _e($oOrder->delivery_houseNumber);

            $aKeys[] = '[order_delivery_houseNumberAddition]';
            $aValues[] = _e($oOrder->delivery_houseNumberAddition);

            $aKeys[] = '[order_delivery_fullAddress]';
            $aValues[] = _e($oOrder->delivery_address . ($oOrder->delivery_houseNumber ? ' ' . $oOrder->delivery_houseNumber : '') . ($oOrder->delivery_houseNumberAddition ? ' ' . $oOrder->delivery_houseNumberAddition : ''));

            $aKeys[] = '[order_delivery_postalCode]';
            $aValues[] = _e($oOrder->delivery_postalCode);

            $aKeys[] = '[order_delivery_city]';
            $aValues[] = _e($oOrder->delivery_city);

            # define the ordered products for the order_productsInfo variable
            $sProducts = '';
            if ($bTest) {
                for ($i = 1; $i < 5; $i++) {
                    $sProducts .= '<tr>';
                    $sProducts .= '<td>Testproduct ' . $i . '</td>';
                    $sProducts .= '<td>' . $i . '</td>';
                    $sProducts .= '<td>' . decimal2valuta($i * 15) . '</td>';
                    $sProducts .= '</tr>';
                }
            } else {
                foreach ($oOrder->getProducts() as $oProduct) {
                    $sProducts .= '<tr>';
                    $sProducts .= '<td>' . $oProduct->productName . '</td>';
                    $sProducts .= '<td>' . $oProduct->amount . '</td>';
                    $sProducts .= '<td>' . decimal2valuta($oProduct->getSubTotalPrice(true)) . '</td>';
                    $sProducts .= '</tr>';
                }
            }

            $aKeys[] = '[order_productsInfo]';
            $aValues[] = '  <table style="width: 600px;">
                                <thead>
                                    <tr>
                                        <th style="text-align: left;">Productnaam</th>
                                        <th style="text-align: left;">Aantal</th>
                                        <th style="text-align: left;">Prijs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ' . $sProducts . '
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2">Aflevermethode: ' . $sDeliveryMethod . '</td>
                                        <td>' . decimal2valuta($fDeliveryPrice) . '</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">Betaalmethode: ' . $sPaymentMethod . '</td>
                                        <td>' . decimal2valuta($fPaymentPrice) . '</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">Totaal</td>
                                        <td>' . decimal2valuta($fTotalPriceWithTax) . '</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">BTW</td>
                                        <td>' . decimal2valuta($fTotalTaxPrice) . '</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">Totaal (excl. BTW)</td>
                                        <td>' . decimal2valuta($fTotalPriceWithoutTax) . '</td>
                                    </tr>
                                </tfoot>
                            </table>';

            $aKeys[] = '[order_customerGoogleMapsLink]';
            $aValues[] = $sCustomerGoogleMapsLink;
        } elseif (!empty($oCustomer)) {
//            $aKeys[] = '[customer_firstName]';
//            $aValues[] = _e($oCustomer->firstName);
//
//            $aKeys[] = '[customer_insertion]';
//            $aValues[] = _e($oCustomer->insertion);
//
//            $aKeys[] = '[customer_lastName]';
//            $aValues[] = _e($oCustomer->lastName);

            $aKeys[] = '[customer_name]';
            $aValues[] = _e($oCustomer->name);
            
            $aKeys[] = '[customer_fullName]';
            $aValues[] = _e($oCustomer->getFullName());
            
            $aKeys[] = '[customer_confirmCode]';
            $aValues[] = _e($oCustomer->confirmCode);

            $aKeys[] = '[customer_email]';
            $aValues[] = _e($oCustomer->email);
        }

        // add extra values for replacement
        foreach ($aExtraValues AS $sKey => $sValue) {
            $aKeys[] = $sKey;
            $aValues[] = $sValue;
        }

        // general settings values
        $aKeys[] = '[CLIENT_HTTP_URL]';
        $aValues[] = _e(CLIENT_HTTP_URL);
        
        $aKeys[] = '[CLIENT_HTTPS_URL]';
        $aValues[] = _e(CLIENT_HTTPS_URL);

        $aKeys[] = '[CLIENT_NAME]';
        $aValues[] = _e(CLIENT_NAME);

        $aKeys[] = '[CLIENT_URL]';
        $aValues[] = _e(CLIENT_URL);

        /* template waarden vervangen */
        $this->subject = str_replace($aKeys, $aValues, $this->subject);
        $this->template = str_replace($aKeys, $aValues, $this->template);
    }

    /**
     * just returns integer, DO NOT USE FOR IS EDITABLE CHECKING
     * return value of editable
     * @return int
     */
    public function getEditable() {
        return $this->editable;
    }

    /**
     * just returns integer, DO NOT USE FOR IS DELETABLE CHECKING
     * return value of deletable
     * @return int
     */
    public function getDeletable() {
        return $this->deletable;
    }

    /**
     * set editable
     * @param boolean $bEditable
     */
    public function setEditable($bEditable) {
        $this->editable = $bEditable;
    }

    /**
     * set deletable
     * @param boolean $bDeletable
     */
    public function setDeletable($bDeletable) {
        $this->deletable = $bDeletable;
    }

}

?>
