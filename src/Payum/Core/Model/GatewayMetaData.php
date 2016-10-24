<?php

namespace Payum\Core\Model;


class GatewayMetaData
{
    Public function getStripeMetaData()
    {
        return array(
            'factory' => '',
            'publishable_key' => '',
            'secret_key' => ''
        );
    }

    public function getAuthorizeMetaData(){
        return array(
            'factory' => '',
            'login_id' => '',
            'transaction_key' => '',
            'sandbox' => true
        );
    }

    public function  getBe2BillMetaData(){
        return array(
            'factory' => '',
            'identifier' => '',
            'password' => '',
            'sandbox' => true
        );
    }

    public function getKlarnaMetaData(){
        return array(
            'factory' => '',
            'merchant_id' => '',
            'secret' => '',
            'terms_uri' => '',
            'checkout_uri' => '',
            'sandbox' => true
        );
    }

    public function getPayexMetaData(){
        return array(
            'factory' => '',
            'account_number' => '',
            'encryption_key' => '',
            'sandbox' => true
        );
    }

    public function getPaypalExpressMetaData(){
        return array(
            'factory' => '',
            'username' => '',
            'password' => '',
            'signature' => '',
            'sandbox' => true
        );
    }

    public function getPaypalProMetaData(){
        return array(
            'factory' => '',
            'username' => '',
            'password' => '',
            'partner' => '',
            'vendor' => '',
            'tender' => '',
            'sandbox' => true
        );
    }

    public function getPaypalRestMetaData(){
        return array(
            'factory' => '',
            'client_id' => '',
            'client_secret' => '',
            'config_path' => ''
        );
    }

    public function getEwayMetaData(){
        return array(
            'factory' => '',
            'user '=>'',
            'refundpassword' => '',
        );
    }

    public function getglobalgatewayMetaData(){
        return array(
            'factory' => '',
            'gatewayid'=>'',
            'password' => '',
            'keyid' => '',
            'HMACkey'=>''
        );
    }

    public function getSecurepayMetaData(){
        return array(
            'factory' => '',
            'merchantid'=>'',
            'password'=>'',

        );
    }
    
    public function getBeanStreamMetaData(){
        return array(
            'factory' => '',
            'merchantid'=>'',
            'username'=>'',
            'pass'=>'',
        );
    }

    public function getAllPayMetaData(){
        return array(
            'factory' => '',
            'merchantid'=>'',
            'hashkey'=>'',
            'hashIV'=>'',
        );
    }

    public function getPaytraceMetaData(){
        return array(
            'factory' => '',
            'username' => '',
            'password' => '',
        );
    }

    Public function getPaySafeMetaData()
    {
        return array(
            'factory' => '',
            'accountnumber' => '',
            'api' => ''
        );
    }

    public function getEwayUKMetaData()
    {
        return array(
            'factory' => '',
            'apikey' => '',
            'password' => ''
        );
    }

    public function getDurangoMetaData()
    {
        return array(
            'factory' => '',
            'login_id' => '',
            'transaction_key' => ''
        );
    }

    public function getOgoneMetaData()
    {
        return array(
            'factory' => '',
            'pspid' => '',
            'userid' => '',
            'password' => '',
            'passphrase' => '',
            'secure' => ''
        );
    }

    public function getOrbitalMetaData(){
        return array(
            'factory' => '',
            'user' => '',
            'terminalid' => '',
            'bin'=>'',
            'currency'=>''
        );
    }

    public function geteProcessingMetaData()
    {
        return array(
            'factory' => '',
            'user' => '',
            'pass' => ''
        );
    }

    public function getInternetSecureMetaData()
    {
        return array(
            'factory' => '',
            'login_id' => '',
            'transaction_key' => ''
        );
    }

    public function getCaledonMetaData()
    {
        return array(
            'factory' => '',
            'user' => ''
        );
    }

    public function getCieloMetaData(){
        return array(
            'factory' => '',
            'merchant' => '',
            'merchantkey' => '',
            'merchanttext'=>'',
            'order'=>'',
            'statementtext'=>'',
        );
    }

    public function getCMCICMetaData(){
        return array(
            'factory' => '',
            'tpe' => '',
            'key' => '',
            'societe'=>''
        );
    }

    public function getDIBSMetaData(){
        return array(
            'factory' => '',
            'merchant' => '',
            'username' => '',
            'password'=>'',
            'md5_k1'=>'',
            'md5_k2'=>'',
        );
    }

    public function getLinkpointMetaData(){
        return array(
            'factory' => '',
            'user' => '',
            'certificate' => '',
        );
    }

    public function getWirecardMetaData(){
        return array(
            'customerId' => '',
            'shopId' => '',
            'secret' => '',
            'password' => '',
        );
    }

    public function getCyberSourceMetaData(){
        return array(
            'factory' => '',
            'user' => '',
            'apiKey' => '',
            'secure' => '',
            'log_request' => '',
            'batch_billing' => '',
            'apicert' => '',
            'apicert_password' => '',
            'ebc_username' => '',
            'ebc_password' => '',
        );
    }

    public function getIpayMetaData(){
        return array(
            'factory' => '',
            'companykey' => '',
            'securitykey01' => '',
            'securitykey02' => '',
            'securitykey03' => '',
            'terminalid' => '',
            'currencyindicator' => '',
        );
    }

    public function  getTranzilaMetaData() {
        return array(
            'factory' => '',
            'seller_payme_id' => '',
            'test_mode' => '',
        );
    }
    public function  getMerchantESolutionMetaData() {
        return array(
            'factory' => '',
            'profile_id' => '',
            'profile_key' => '',
        );
    }
}