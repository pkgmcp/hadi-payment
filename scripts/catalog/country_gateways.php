<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Country-Specific Gateway Pool
|--------------------------------------------------------------------------
|
| Local, country-specific gateways keyed by ISO2 country code. Each value is
| [slug => ['name' => ..., 'type' => ...]]. These are combined with the
| global + regional pools by generate_catalog.php.
|
*/

return [

    'AF' => [
        'm-paisa' => ['name' => 'M-Paisa', 'type' => 'mobile'],
        'hpl' => ['name' => 'HPL', 'type' => 'mobile'],
        'afghan-bank' => ['name' => 'Afghanistan Bank', 'type' => 'bank'],
        'azizi' => ['name' => 'Azizi Bank', 'type' => 'bank'],
        'ghazanfar' => ['name' => 'Ghazanfar Bank', 'type' => 'bank'],
        'new-afghan' => ['name' => 'New Kabul Bank', 'type' => 'bank'],
        'hesab' => ['name' => 'Hesab', 'type' => 'api'],
    ],

    'AL' => [
        'bkt' => ['name' => 'Bank of Tirana', 'type' => 'bank'],
        'raiffeisen-al' => ['name' => 'Raiffeisen Albania', 'type' => 'bank'],
        'ocbsh' => ['name' => 'OCB', 'type' => 'bank'],
        'paymentez-al' => ['name' => 'Paymentez Albania', 'type' => 'api'],
        'alp-al' => ['name' => 'ALP', 'type' => 'api'],
    ],

    'DZ' => [
        'naps-dz' => ['name' => 'NAPS Algeria', 'type' => 'api'],
        'edahab' => ['name' => 'E-Dahab', 'type' => 'mobile'],
        'badr' => ['name' => 'BADR Bank', 'type' => 'bank'],
        'agence' => ['name' => 'Agence Postale', 'type' => 'api'],
        'algerie-pay' => ['name' => 'Algérie Pay', 'type' => 'api'],
    ],

    'AD' => [
        'andorra-bank' => ['name' => 'Andbank', 'type' => 'bank'],
        'mora' => ['name' => 'MoraBanc', 'type' => 'bank'],
        'vall' => ['name' => 'Vall Banc', 'type' => 'bank'],
        'cr-banc' => ['name' => 'Crèdit Andorrà', 'type' => 'bank'],
    ],

    'AO' => [
        'multicaixa-ao' => ['name' => 'Multicaixa', 'type' => 'mobile'],
        'emola-ao' => ['name' => 'e-Mola', 'type' => 'mobile'],
        'banco-bic' => ['name' => 'Banco BIC', 'type' => 'bank'],
        'bai' => ['name' => 'BAI', 'type' => 'bank'],
        'unitel-ao' => ['name' => 'Unitel', 'type' => 'mobile'],
    ],

    'AG' => [
        'abib' => ['name' => 'ABIB', 'type' => 'bank'],
        'abt' => ['name' => 'Antigua Bank', 'type' => 'bank'],
        'ecab' => ['name' => 'ECAB', 'type' => 'bank'],
        'antigua-pay' => ['name' => 'AntiguaPay', 'type' => 'api'],
    ],

    'AR' => [
        'mercadopago-ar' => ['name' => 'Mercado Pago Argentina', 'type' => 'api'],
        'todo-pago-ar' => ['name' => 'Todo Pago', 'type' => 'api'],
        'uala' => ['name' => 'Ualá', 'type' => 'wallet'],
        'rapipago' => ['name' => 'Rapipago', 'type' => 'bank'],
        'pago-facil' => ['name' => 'Pago Fácil', 'type' => 'bank'],
        'brubank' => ['name' => 'BruBank', 'type' => 'wallet'],
        'modo' => ['name' => 'Modo', 'type' => 'mobile'],
        'prisma' => ['name' => 'Prisma Medios de Pago', 'type' => 'api'],
        'firstdata-ar' => ['name' => 'First Data Argentina', 'type' => 'api'],
    ],

    'AM' => [
        'arar' => ['name' => 'Ararat Bank', 'type' => 'bank'],
        'ameriabank' => ['name' => 'Ameriabank', 'type' => 'bank'],
        'idram-am' => ['name' => 'Idram', 'type' => 'api'],
        'telcell-am' => ['name' => 'TelCell', 'type' => 'mobile'],
        'mobil-dram' => ['name' => 'MobilDram', 'type' => 'wallet'],
        'armex' => ['name' => 'Armex', 'type' => 'api'],
    ],

    'AU' => [
        'commbank-au' => ['name' => 'Commonwealth Bank', 'type' => 'bank'],
        'westpac-au' => ['name' => 'Westpac', 'type' => 'bank'],
        'anz-au' => ['name' => 'ANZ', 'type' => 'bank'],
        'nab-au' => ['name' => 'NAB', 'type' => 'bank'],
        'bpay-au' => ['name' => 'BPAY', 'type' => 'bank'],
        'efpos' => ['name' => 'EFTPOS', 'type' => 'bank'],
        'zai' => ['name' => 'Zai', 'type' => 'api'],
        'ezeepay-au' => ['name' => 'eZeePay', 'type' => 'api'],
        'fat-zebra-au' => ['name' => 'Fat Zebra', 'type' => 'api'],
        'westpac-widget' => ['name' => 'Westpac Pay', 'type' => 'api'],
    ],

    'AT' => [
        'eps-at' => ['name' => 'EPS', 'type' => 'bank'],
        'giropay-at' => ['name' => 'Giropay Austria', 'type' => 'bank'],
        'paydirekt-at' => ['name' => 'Paydirekt Austria', 'type' => 'bank'],
        'raiffeisen-at' => ['name' => 'Raiffeisen Austria', 'type' => 'bank'],
        'erstebank' => ['name' => 'Erste Bank', 'type' => 'bank'],
        'bawag' => ['name' => 'BAWAG', 'type' => 'bank'],
        'bluemoney' => ['name' => 'Blue Money', 'type' => 'api'],
        'paylife' => ['name' => 'PayLife', 'type' => 'api'],
    ],

    'AZ' => [
        'azercell' => ['name' => 'Azercell', 'type' => 'mobile'],
        'bakcell' => ['name' => 'Bakcell', 'type' => 'mobile'],
        'nab-bank' => ['name' => 'International Bank of Azerbaijan', 'type' => 'bank'],
        'kapitalbank-az' => ['name' => 'Kapital Bank', 'type' => 'api'],
        'bank-of-baku' => ['name' => 'Bank of Baku', 'type' => 'bank'],
        'pasabank' => ['name' => 'Pasha Bank', 'type' => 'bank'],
        'milli-pay-az' => ['name' => 'MilliPay', 'type' => 'api'],
    ],

    'BS' => [
        'scotiabank-bs' => ['name' => 'Scotiabank Bahamas', 'type' => 'bank'],
        'cbb' => ['name' => 'Commonwealth Bank Bahamas', 'type' => 'bank'],
        'rbc-bs' => ['name' => 'RBC Bahamas', 'type' => 'bank'],
        'island-pay' => ['name' => 'Island Pay', 'type' => 'api'],
    ],

    'BH' => [
        'benefitpay-bh' => ['name' => 'BenefitPay', 'type' => 'api'],
        'bbk' => ['name' => 'BBK', 'type' => 'bank'],
        'nbb-bh' => ['name' => 'NBB', 'type' => 'bank'],
        'stcpay-bh' => ['name' => 'STC Pay Bahrain', 'type' => 'mobile'],
        'bahrain-pay' => ['name' => 'Bahrain Pay', 'type' => 'api'],
        'beyon' => ['name' => 'Beyon Money', 'type' => 'wallet'],
    ],

    'BD' => [
        'bkash' => ['name' => 'bKash', 'type' => 'mobile', 'driver' => 'Hadi\\Payment\\Gateways\\BkashGateway'],
        'banglaqr' => ['name' => 'BanglaQR', 'type' => 'api', 'driver' => 'Hadi\\Payment\\Gateways\\BanglaQrGateway'],
        'nagad' => ['name' => 'Nagad', 'type' => 'mobile', 'driver' => 'Hadi\\Payment\\Gateways\\NagadGateway'],
        'rocket' => ['name' => 'Rocket', 'type' => 'mobile'],
        'sslcommerz' => ['name' => 'SSLCommerz', 'type' => 'api'],
        'shurjopay' => ['name' => 'ShurjoPay', 'type' => 'api'],
        'aamarpay' => ['name' => 'AamarPay', 'type' => 'api'],
        'upay' => ['name' => 'uPay', 'type' => 'mobile'],
        'surecash' => ['name' => 'SureCash', 'type' => 'mobile'],
        'mcash-bd' => ['name' => 'MCash', 'type' => 'mobile'],
        'mycash-bd' => ['name' => 'MyCash', 'type' => 'mobile'],
        'ucash-bd' => ['name' => 'UCash', 'type' => 'mobile'],
        'dutch-bangla' => ['name' => 'Dutch Bangla Bank', 'type' => 'bank'],
        'brac-bank' => ['name' => 'BRAC Bank', 'type' => 'bank'],
        'sbl-bd' => ['name' => 'Sonali Bank', 'type' => 'bank'],
        'ibl-bd' => ['name' => 'Islami Bank', 'type' => 'bank'],
        'ebl-bd' => ['name' => 'Eastern Bank', 'type' => 'bank'],
        'prime-bank-bd' => ['name' => 'Prime Bank', 'type' => 'bank'],
        'bgmea' => ['name' => 'BGMEA', 'type' => 'api'],
        'bkash-merchant' => ['name' => 'bKash Merchant', 'type' => 'api'],
        'nagad-merchant' => ['name' => 'Nagad Merchant', 'type' => 'api'],
    ],

    'BB' => [
        'cb-bb' => ['name' => 'CIBC FirstCaribbean Barbados', 'type' => 'bank'],
        'rbc-bb' => ['name' => 'RBC Barbados', 'type' => 'bank'],
        'scotiabank-bb' => ['name' => 'Scotiabank Barbados', 'type' => 'bank'],
        'pay-bb' => ['name' => 'Pay Barbados', 'type' => 'api'],
    ],

    'BY' => [
        'belagroprombank-by' => ['name' => 'Belagroprombank', 'type' => 'api'],
        'belnap' => ['name' => 'BelNAP', 'type' => 'bank'],
        'belswift' => ['name' => 'Belswift', 'type' => 'bank'],
        'priorbank' => ['name' => 'Priorbank', 'type' => 'bank'],
        'belarusbank' => ['name' => 'Belarusbank', 'type' => 'bank'],
        'erip' => ['name' => 'ERIP', 'type' => 'api'],
    ],

    'BE' => [
        'bancontact-be' => ['name' => 'Bancontact', 'type' => 'bank'],
        'belfius' => ['name' => 'Belfius', 'type' => 'bank'],
        'kbc' => ['name' => 'KBC', 'type' => 'bank'],
        'payconiq' => ['name' => 'Payconiq', 'type' => 'wallet'],
        'ing-be' => ['name' => 'ING Belgium', 'type' => 'bank'],
        'itsme' => ['name' => 'itsme', 'type' => 'api'],
        'worldpay-be' => ['name' => 'Worldpay Belgium', 'type' => 'api'],
    ],

    'BZ' => [
        'belize-bank' => ['name' => 'Belize Bank', 'type' => 'bank'],
        'atlantic-bz' => ['name' => 'Atlantic Bank', 'type' => 'bank'],
        'scotiabank-bz' => ['name' => 'Scotiabank Belize', 'type' => 'bank'],
        'belize-digital' => ['name' => 'Belize Digital Pay', 'type' => 'api'],
        'digicel-bz' => ['name' => 'Digicel Belize Mobile Money', 'type' => 'mobile'],
    ],

    'BJ' => [
        'mtn-bj' => ['name' => 'MTN Mobile Money Benin', 'type' => 'mobile'],
        'moov-bj' => ['name' => 'Moov Money Benin', 'type' => 'mobile'],
        'sbee' => ['name' => 'SBEE', 'type' => 'bank'],
        'bms-bj' => ['name' => 'Bank of Africa Benin', 'type' => 'bank'],
        'celpaid' => ['name' => 'Celpaid', 'type' => 'mobile'],
    ],

    'BT' => [
        'bob-bt' => ['name' => 'Bank of Bhutan', 'type' => 'api'],
        'bdb' => ['name' => 'Bhutan Development Bank', 'type' => 'bank'],
        't-bank' => ['name' => 'T Bank', 'type' => 'bank'],
        'bhutan-pay' => ['name' => 'Bhutan Pay', 'type' => 'api'],
        'm-bank' => ['name' => 'Mobile Wallet Bhutan', 'type' => 'wallet'],
    ],

    'BO' => [
        'pix-bolivia' => ['name' => 'Pix Bolivia', 'type' => 'bank'],
        'banco-sol' => ['name' => 'BancoSol', 'type' => 'bank'],
        'yape-bo' => ['name' => 'Yape Bolivia', 'type' => 'wallet'],
        'tigo-money-bo' => ['name' => 'Tigo Money Bolivia', 'type' => 'mobile'],
        'entel-bo' => ['name' => 'Entel', 'type' => 'mobile'],
    ],

    'BA' => [
        'procredit' => ['name' => 'ProCredit Bank', 'type' => 'bank'],
        'bih-bank' => ['name' => 'Raiffeisen BiH', 'type' => 'bank'],
        'uni-bank' => ['name' => 'UniCredit BiH', 'type' => 'bank'],
        'bh-pay' => ['name' => 'BiH Pay', 'type' => 'api'],
    ],

    'BW' => [
        'mascom-bw' => ['name' => 'Mascom MyZaka', 'type' => 'mobile'],
        'orange-bw' => ['name' => 'Orange Money Botswana', 'type' => 'mobile'],
        'fchb' => ['name' => 'First National Bank Botswana', 'type' => 'bank'],
        'barclays-bw' => ['name' => 'Absa Botswana', 'type' => 'bank'],
        'moyo' => ['name' => 'Moyo', 'type' => 'mobile'],
    ],

    'BR' => [
        'pix-br' => ['name' => 'Pix (Brazil)', 'type' => 'bank'],
        'boleto' => ['name' => 'Boleto Bancário', 'type' => 'bank'],
        'cielo-br' => ['name' => 'Cielo', 'type' => 'api'],
        'pagseguro-br' => ['name' => 'PagSeguro', 'type' => 'api'],
        'stone-br' => ['name' => 'Stone', 'type' => 'api'],
        'rede-br' => ['name' => 'Rede', 'type' => 'api'],
        'getnet-br' => ['name' => 'Getnet', 'type' => 'api'],
        'itau-br' => ['name' => 'Itaú', 'type' => 'bank'],
        'bradesco-br' => ['name' => 'Bradesco', 'type' => 'bank'],
        'picpay' => ['name' => 'PicPay', 'type' => 'mobile'],
        'nubank' => ['name' => 'Nubank', 'type' => 'wallet'],
        'inter-br' => ['name' => 'Banco Inter', 'type' => 'bank'],
        'elo' => ['name' => 'Elo', 'type' => 'card'],
        'hipercard' => ['name' => 'Hipercard', 'type' => 'card'],
        '99pay' => ['name' => '99Pay', 'type' => 'mobile'],
    ],

    'BN' => [
        'bibd-bn' => ['name' => 'BIBD', 'type' => 'mobile'],
        'baiduri' => ['name' => 'Baiduri Bank', 'type' => 'bank'],
        'scb-bn' => ['name' => 'Standard Chartered Brunei', 'type' => 'bank'],
        'brunei-pay' => ['name' => 'Brunei Pay', 'type' => 'api'],
    ],

    'BG' => [
        'borica-bg' => ['name' => 'Borica', 'type' => 'api'],
        'dsb-bg' => ['name' => 'DSK Bank', 'type' => 'bank'],
        'unicredit-bg' => ['name' => 'UniCredit Bulbank', 'type' => 'bank'],
        'fibank' => ['name' => 'First Investment Bank', 'type' => 'bank'],
        'payworks' => ['name' => 'Payworks', 'type' => 'api'],
        'easypay-bg' => ['name' => 'ePay.bg', 'type' => 'api'],
    ],

    'BF' => [
        'orange-bf' => ['name' => 'Orange Money Burkina', 'type' => 'mobile'],
        'mtn-bf' => ['name' => 'MTN MoMo Burkina', 'type' => 'mobile'],
        'moov-bf' => ['name' => 'Moov Money Burkina', 'type' => 'mobile'],
        'coris' => ['name' => 'Coris Bank', 'type' => 'bank'],
        'ecobank-bf' => ['name' => 'Ecobank Burkina', 'type' => 'bank'],
    ],

    'BI' => [
        'lumu-cash' => ['name' => 'Lumicash', 'type' => 'mobile'],
        'm-pesa-bi' => ['name' => 'M-Pesa Burundi', 'type' => 'mobile'],
        'bancobu' => ['name' => 'Bancobu', 'type' => 'bank'],
        'ecobank-bi' => ['name' => 'Ecobank Burundi', 'type' => 'bank'],
    ],

    'CV' => [
        'bca-cv' => ['name' => 'BCA Cabo Verde', 'type' => 'bank'],
        'novobanco' => ['name' => 'Novo Banco', 'type' => 'bank'],
        't-move' => ['name' => 'T-Move', 'type' => 'mobile'],
        'cabover-pay' => ['name' => 'CaboPay', 'type' => 'api'],
    ],

    'KH' => [
        'bakong-kh' => ['name' => 'Bakong', 'type' => 'api'],
        'aba-bank' => ['name' => 'ABA Bank', 'type' => 'bank'],
        'acleda' => ['name' => 'ACLEDA', 'type' => 'bank'],
        'wing-kh' => ['name' => 'Wing', 'type' => 'mobile'],
        'pi-pay-kh' => ['name' => 'Pi Pay', 'type' => 'mobile'],
        'canadia' => ['name' => 'Canadia Bank', 'type' => 'bank'],
    ],

    'CM' => [
        'mtn-cm-cash' => ['name' => 'MTN MoMo Cameroon', 'type' => 'mobile'],
        'orange-cm' => ['name' => 'Orange Money Cameroon', 'type' => 'mobile'],
        'yup' => ['name' => 'YUUP', 'type' => 'mobile'],
        'ecobank-cm' => ['name' => 'Ecobank Cameroon', 'type' => 'bank'],
        'scb-cm' => ['name' => 'Société Générale Cameroon', 'type' => 'bank'],
    ],

    'CA' => [
        'interac' => ['name' => 'Interac', 'type' => 'bank'],
        'moneris-ca' => ['name' => 'Moneris', 'type' => 'api'],
        'paytrie' => ['name' => 'PayTrie', 'type' => 'api'],
        'td-ca' => ['name' => 'TD Bank', 'type' => 'bank'],
        'rbc-ca' => ['name' => 'RBC', 'type' => 'bank'],
        'bmo-ca' => ['name' => 'BMO', 'type' => 'bank'],
        'scotia-ca' => ['name' => 'Scotiabank', 'type' => 'bank'],
        'cibc-ca' => ['name' => 'CIBC', 'type' => 'bank'],
        'snaplii' => ['name' => 'Snaplii', 'type' => 'api'],
        'hopepay' => ['name' => 'Hope Pay', 'type' => 'wallet'],
        'squircle' => ['name' => 'Squircle', 'type' => 'mobile'],
    ],

    'CF' => [
        'orange-cf' => ['name' => 'Orange Money Central African Republic', 'type' => 'mobile'],
        'mtn-cf' => ['name' => 'MTN MoMo CAR', 'type' => 'mobile'],
        'ucb' => ['name' => 'Union Bank CAR', 'type' => 'bank'],
        'ecobank-cf' => ['name' => 'Ecobank CAR', 'type' => 'bank'],
    ],

    'TD' => [
        'orange-td' => ['name' => 'Orange Money Chad', 'type' => 'mobile'],
        'tawasal' => ['name' => 'Tawasal', 'type' => 'mobile'],
        'btcd' => ['name' => 'BTCD', 'type' => 'bank'],
        'ecobank-td' => ['name' => 'Ecobank Chad', 'type' => 'bank'],
    ],

    'CL' => [
        'webpay-cl' => ['name' => 'Webpay Plus', 'type' => 'api'],
        'khipu' => ['name' => 'Khipu', 'type' => 'mobile'],
        'mach-cl' => ['name' => 'Mach', 'type' => 'mobile'],
        'flow-cl' => ['name' => 'Flow', 'type' => 'api'],
        'banco-estado' => ['name' => 'BancoEstado', 'type' => 'bank'],
        'redcompra-cl' => ['name' => 'Redcompra', 'type' => 'bank'],
        'multicaja' => ['name' => 'Multicaja', 'type' => 'api'],
        'tenpo' => ['name' => 'Tenpo', 'type' => 'mobile'],
    ],

    'CN' => [
        'alipay-cn-local' => ['name' => 'Alipay China', 'type' => 'wallet'],
        'wechatpay-cn' => ['name' => 'WeChat Pay China', 'type' => 'wallet'],
        'unionpay-cn' => ['name' => 'UnionPay China', 'type' => 'card'],
        'tenpay-cn' => ['name' => 'Tenpay', 'type' => 'wallet'],
        'cmb' => ['name' => 'China Merchants Bank', 'type' => 'bank'],
        'icbc' => ['name' => 'ICBC', 'type' => 'bank'],
        'ccb' => ['name' => 'CCB', 'type' => 'bank'],
        'abc-china' => ['name' => 'ABC China', 'type' => 'bank'],
        'boc-china' => ['name' => 'Bank of China', 'type' => 'bank'],
        'qutoutiao' => ['name' => 'QuTouTiao', 'type' => 'wallet'],
    ],

    'CO' => [
        'pse-co' => ['name' => 'PSE', 'type' => 'bank'],
        'nequi-co' => ['name' => 'Nequi', 'type' => 'mobile'],
        'daviplata' => ['name' => 'DaviPlata', 'type' => 'mobile'],
        'addi-co' => ['name' => 'Addi', 'type' => 'api'],
        'payu-co' => ['name' => 'PayU Colombia', 'type' => 'api'],
        'bancolombia-co' => ['name' => 'Bancolombia', 'type' => 'bank'],
        'sistarbanc' => ['name' => 'Sistarbanc', 'type' => 'api'],
        'e-pago' => ['name' => 'E-Pago', 'type' => 'api'],
        'rappipay-co' => ['name' => 'RappiPay', 'type' => 'mobile'],
    ],

    'KM' => [
        'm-pesa-km' => ['name' => 'M-Pesa Comoros', 'type' => 'mobile'],
        'bic-km' => ['name' => 'BIC Comoros', 'type' => 'bank'],
        'snps' => ['name' => 'SNPS', 'type' => 'bank'],
        'comores-telecom' => ['name' => 'Comores Telecom', 'type' => 'mobile'],
    ],

    'CG' => [
        'mtn-cg' => ['name' => 'MTN MoMo Congo', 'type' => 'mobile'],
        'airtel-cg' => ['name' => 'Airtel Money Congo', 'type' => 'mobile'],
        'bceao' => ['name' => 'BCEAO Congo', 'type' => 'bank'],
        'ecobank-cg' => ['name' => 'Ecobank Congo', 'type' => 'bank'],
    ],

    'CD' => [
        'm-pesa-cd-local' => ['name' => 'M-Pesa DR Congo', 'type' => 'mobile'],
        'airtel-cd' => ['name' => 'Airtel Money DR Congo', 'type' => 'mobile'],
        'orange-cd' => ['name' => 'Orange Money DR Congo', 'type' => 'mobile'],
        'africell-cd' => ['name' => 'Africell Money DR Congo', 'type' => 'mobile'],
        'rawbank' => ['name' => 'Rawbank', 'type' => 'bank'],
        'equity-bcd' => ['name' => 'Equity BCDC', 'type' => 'bank'],
    ],

    'CR' => [
        'sinpe' => ['name' => 'SINPE', 'type' => 'mobile'],
        'bac-cr' => ['name' => 'BAC Credomatic', 'type' => 'api'],
        'bn-cr' => ['name' => 'Banco Nacional', 'type' => 'bank'],
        'bcr' => ['name' => 'BCR', 'type' => 'bank'],
        'davivienda-cr' => ['name' => 'Davivienda', 'type' => 'bank'],
        'paymentez-cr' => ['name' => 'Paymentez', 'type' => 'api'],
        'sinpe-movil' => ['name' => 'SINPE Móvil', 'type' => 'mobile'],
    ],

    'CI' => [
        'orange-ci-cash' => ['name' => 'Orange Money Côte d\'Ivoire', 'type' => 'mobile'],
        'mtn-ci' => ['name' => 'MTN MoMo Côte d\'Ivoire', 'type' => 'mobile'],
        'wave-ci' => ['name' => 'Wave Côte d\'Ivoire', 'type' => 'mobile'],
        'moov-ci' => ['name' => 'Moov Money Côte d\'Ivoire', 'type' => 'mobile'],
        'bicici' => ['name' => 'BICICI', 'type' => 'bank'],
        'sib-ci' => ['name' => 'SIB', 'type' => 'bank'],
        'ci-pay' => ['name' => 'CI Pay', 'type' => 'api'],
    ],

    'HR' => [
        'payway-hr' => ['name' => 'PayWay', 'type' => 'api'],
        'keks' => ['name' => 'KEKS Pay', 'type' => 'bank'],
        'aircash' => ['name' => 'Aircash', 'type' => 'wallet'],
        'pbz' => ['name' => 'PBZ', 'type' => 'bank'],
        'zaba' => ['name' => 'Zagrebačka Banka', 'type' => 'bank'],
        'erste-hr' => ['name' => 'Erste Croatia', 'type' => 'bank'],
    ],

    'CU' => [
        'transfermóvil-cu' => ['name' => 'Transfermóvil', 'type' => 'mobile'],
        'enzona-cu' => ['name' => 'Enzona', 'type' => 'api'],
        'bandec' => ['name' => 'BANDEC', 'type' => 'bank'],
        'bmet' => ['name' => 'BMET', 'type' => 'bank'],
        'zonapago' => ['name' => 'Zonapago', 'type' => 'api'],
    ],

    'CY' => [
        'jcc-cy' => ['name' => 'JCC', 'type' => 'api'],
        'bank-of-cyprus' => ['name' => 'Bank of Cyprus', 'type' => 'bank'],
        'hcbc' => ['name' => 'Hellenic Bank', 'type' => 'bank'],
        'eur-cy' => ['name' => 'Eurobank Cyprus', 'type' => 'bank'],
        'ccv-cy' => ['name' => 'CCV Cyprus', 'type' => 'api'],
    ],

    'CZ' => [
        'comgate-cz' => ['name' => 'ComGate', 'type' => 'api'],
        'gopay-cz' => ['name' => 'GoPay', 'type' => 'api'],
        'csob' => ['name' => 'ČSOB', 'type' => 'bank'],
        'kb-cz' => ['name' => 'Komerční Banka', 'type' => 'bank'],
        'raiffeisen-cz' => ['name' => 'Raiffeisen Czech', 'type' => 'bank'],
        'paysec' => ['name' => 'PaySec', 'type' => 'api'],
        'bankart' => ['name' => 'Bankart', 'type' => 'api'],
    ],

    'DK' => [
        'nets-dk' => ['name' => 'Nets', 'type' => 'api'],
        'mobilepay-dk' => ['name' => 'MobilePay', 'type' => 'wallet'],
        'klarna-dk' => ['name' => 'Klarna Denmark', 'type' => 'api'],
        'dankort' => ['name' => 'Dankort', 'type' => 'card'],
        'danske' => ['name' => 'Danske Bank', 'type' => 'bank'],
        'nordea-dk' => ['name' => 'Nordea Denmark', 'type' => 'bank'],
        'viabill' => ['name' => 'ViaBill', 'type' => 'api'],
    ],

    'DJ' => [
        'djeepay-dj' => ['name' => 'Djeepay', 'type' => 'mobile'],
        'bcd-dj' => ['name' => 'BCD', 'type' => 'bank'],
        'dahabshiil' => ['name' => 'Dahabshiil', 'type' => 'api'],
        'dj-tel' => ['name' => 'Djibouti Telecom', 'type' => 'mobile'],
    ],

    'DM' => [
        'firstcaribbean-dm' => ['name' => 'CIBC FirstCaribbean Dominica', 'type' => 'bank'],
        'abank-dm' => ['name' => 'AID Bank', 'type' => 'bank'],
        'nbs-dm' => ['name' => 'National Bank of Dominica', 'type' => 'bank'],
        'dom-pay' => ['name' => 'Dominica Pay', 'type' => 'api'],
    ],

    'DO' => [
        'pago-do' => ['name' => 'Pago República Dominicana', 'type' => 'api'],
        'bhd' => ['name' => 'BHD León', 'type' => 'bank'],
        'popular' => ['name' => 'Banco Popular', 'type' => 'bank'],
        'reservas' => ['name' => 'Banreservas', 'type' => 'bank'],
        'magicpay' => ['name' => 'Magic Pay', 'type' => 'api'],
        'paymentez-do' => ['name' => 'Paymentez Dominicana', 'type' => 'api'],
    ],

    'EC' => [
        'payphone-ec' => ['name' => 'PayPhone', 'type' => 'mobile'],
        'pago-ec' => ['name' => 'PagoEfectivo', 'type' => 'api'],
        'banco-pichincha' => ['name' => 'Banco Pichincha', 'type' => 'bank'],
        'produbanco' => ['name' => 'Produbanco', 'type' => 'bank'],
        'ecu-cred' => ['name' => 'EcuCred', 'type' => 'api'],
        'daviplata-ec' => ['name' => 'DaviPlata Ecuador', 'type' => 'mobile'],
    ],

    'EG' => [
        'fawry-eg' => ['name' => 'Fawry', 'type' => 'api'],
        'paymob-eg' => ['name' => 'Paymob Egypt', 'type' => 'api'],
        'vodafone-eg-cash' => ['name' => 'Vodafone Cash Egypt', 'type' => 'mobile'],
        'orange-eg-cash' => ['name' => 'Orange Money Egypt', 'type' => 'mobile'],
        'cib-eg' => ['name' => 'CIB Egypt', 'type' => 'bank'],
        'nbe-eg' => ['name' => 'NBE', 'type' => 'bank'],
        'banque-misr-eg' => ['name' => 'Banque Misr', 'type' => 'bank'],
        'amwal' => ['name' => 'Amwal', 'type' => 'api'],
        'tele-cash' => ['name' => 'TeleCash', 'type' => 'api'],
        'e-wallet-eg' => ['name' => 'e-Wallet Egypt', 'type' => 'wallet'],
    ],

    'SV' => [
        'bac-sv' => ['name' => 'BAC Credomatic El Salvador', 'type' => 'api'],
        'banco-agricola' => ['name' => 'Banco Agrícola', 'type' => 'bank'],
        'banco-cuscatlan' => ['name' => 'Banco Cuscatlán', 'type' => 'bank'],
        'banco-industrial-sv' => ['name' => 'Banco Industrial', 'type' => 'bank'],
        'chivo' => ['name' => 'Chivo Wallet', 'type' => 'mobile'],
        'paysv' => ['name' => 'PaySV', 'type' => 'api'],
    ],

    'GQ' => [
        'ge-pay' => ['name' => 'GE Pay', 'type' => 'api'],
        'bge' => ['name' => 'BGE', 'type' => 'bank'],
        'orange-gq' => ['name' => 'Orange Money Equatorial Guinea', 'type' => 'mobile'],
        'mtn-gq' => ['name' => 'MTN MoMo Equatorial Guinea', 'type' => 'mobile'],
    ],

    'ER' => [
        'himiwal' => ['name' => 'Himawwal', 'type' => 'mobile'],
        'cbe-er' => ['name' => 'CBE Eritrea', 'type' => 'bank'],
        'er-telecom' => ['name' => 'Eritrea Telecom', 'type' => 'mobile'],
        'bancа-er' => ['name' => 'Eritrean Banks', 'type' => 'bank'],
    ],

    'EE' => [
        'maksekeskus-ee' => ['name' => 'Maksekeskus', 'type' => 'api'],
        'lhv' => ['name' => 'LHV', 'type' => 'bank'],
        'swedbank-ee' => ['name' => 'Swedbank Estonia', 'type' => 'bank'],
        'seb-ee' => ['name' => 'SEB Estonia', 'type' => 'bank'],
        'estpay' => ['name' => 'Estonian Payment Services', 'type' => 'api'],
    ],

    'SZ' => [
        'mtn-sz' => ['name' => 'MTN Mobile Money Eswatini', 'type' => 'mobile'],
        'swazi' => ['name' => 'Swazi Bank', 'type' => 'bank'],
        'sdb-sz' => ['name' => 'Swaziland Development Bank', 'type' => 'bank'],
        'eswatini-pay' => ['name' => 'Eswatini Pay', 'type' => 'api'],
    ],

    'ET' => [
        'telebirr-et' => ['name' => 'Telebirr', 'type' => 'mobile'],
        'cbe-birr-et' => ['name' => 'CBE Birr', 'type' => 'mobile'],
        'hello-cash-et' => ['name' => 'HelloCash', 'type' => 'mobile'],
        'awash' => ['name' => 'Awash Bank', 'type' => 'bank'],
        'dashen' => ['name' => 'Dashen Bank', 'type' => 'bank'],
        'cbp' => ['name' => 'Commercial Bank of Ethiopia', 'type' => 'bank'],
        'eth-switch' => ['name' => 'Ethio Switch', 'type' => 'api'],
        'm-birr' => ['name' => 'M-Birr', 'type' => 'mobile'],
    ],

    'FJ' => [
        'my-fijibank' => ['name' => 'BSP Fiji', 'type' => 'bank'],
        'westpac-fj-local' => ['name' => 'Westpac Fiji', 'type' => 'bank'],
        'anf' => ['name' => 'ANZ Fiji', 'type' => 'bank'],
        'm-paisa-fj' => ['name' => 'M-Paisa Fiji', 'type' => 'mobile'],
        'voda-cash-fj' => ['name' => 'Vodafone Cash Fiji', 'type' => 'mobile'],
        'tappay' => ['name' => 'Tap Pay', 'type' => 'api'],
    ],

    'FI' => [
        'paytrail-fi' => ['name' => 'Paytrail', 'type' => 'api'],
        'checkout-fi' => ['name' => 'Checkout Finland', 'type' => 'api'],
        'nordea-fi' => ['name' => 'Nordea', 'type' => 'bank'],
        'op-fi' => ['name' => 'OP Financial', 'type' => 'bank'],
        'siirto' => ['name' => 'Siirto', 'type' => 'bank'],
        'vaimo' => ['name' => 'Vaimo Pay', 'type' => 'api'],
    ],

    'FR' => [
        'payplug-fr' => ['name' => 'PayPlug', 'type' => 'api'],
        'lydia-fr' => ['name' => 'Lydia', 'type' => 'wallet'],
        'paylib' => ['name' => 'Paylib', 'type' => 'wallet'],
        'bnp' => ['name' => 'BNP Paribas', 'type' => 'bank'],
        'credit-agricole' => ['name' => 'Crédit Agricole', 'type' => 'bank'],
        'societe-generale' => ['name' => 'Société Générale', 'type' => 'bank'],
        'dalpay' => ['name' => 'DalPay', 'type' => 'api'],
        'payzen' => ['name' => 'PayZen', 'type' => 'api'],
        'sip-fr' => ['name' => 'System Pay', 'type' => 'api'],
    ],

    'GA' => [
        'mobigis' => ['name' => 'Mobigis', 'type' => 'mobile'],
        'airtel-ga' => ['name' => 'Airtel Money Gabon', 'type' => 'mobile'],
        'bicig' => ['name' => 'BICIG', 'type' => 'bank'],
        'ucbg' => ['name' => 'UCBG', 'type' => 'bank'],
        'gabon-pay' => ['name' => 'Gabon Pay', 'type' => 'api'],
    ],

    'GM' => [
        'q-money-gm' => ['name' => 'Q Money', 'type' => 'mobile'],
        'afrimoney-gm' => ['name' => 'Afrimoney Gambia', 'type' => 'mobile'],
        'gtb-gm' => ['name' => 'GTBank Gambia', 'type' => 'bank'],
        'ecobank-gm' => ['name' => 'Ecobank Gambia', 'type' => 'bank'],
    ],

    'GE' => [
        'tbc-ge' => ['name' => 'TBC Pay', 'type' => 'api'],
        'bank-of-georgia-ge' => ['name' => 'Bank of Georgia', 'type' => 'api'],
        'liberty-bank' => ['name' => 'Liberty Bank', 'type' => 'bank'],
        'kartuli' => ['name' => 'Kartuli Bank', 'type' => 'bank'],
        'gvpay' => ['name' => 'GVPay', 'type' => 'api'],
        'spacepay' => ['name' => 'SpacePay', 'type' => 'api'],
        'aict' => ['name' => 'AICT', 'type' => 'api'],
    ],

    'DE' => [
        'giropay-de' => ['name' => 'Giropay', 'type' => 'bank'],
        'paydirekt-de' => ['name' => 'Paydirekt', 'type' => 'bank'],
        'klarna-de' => ['name' => 'Klarna Germany', 'type' => 'api'],
        'paypal-de' => ['name' => 'PayPal Germany', 'type' => 'api'],
        'deutsche-bank' => ['name' => 'Deutsche Bank', 'type' => 'bank'],
        'commerzbank' => ['name' => 'Commerzbank', 'type' => 'bank'],
        'sparkasse' => ['name' => 'Sparkasse', 'type' => 'bank'],
        'barzahlen' => ['name' => 'Barzahlen', 'type' => 'api'],
        'ratepay' => ['name' => 'Ratepay', 'type' => 'api'],
        'sofort-de' => ['name' => 'Sofort Germany', 'type' => 'bank'],
        'billie' => ['name' => 'Billie', 'type' => 'api'],
        'payment-network' => ['name' => 'Payment Network', 'type' => 'api'],
    ],

    'GH' => [
        'mtn-gh-momo' => ['name' => 'MTN Mobile Money Ghana', 'type' => 'mobile'],
        'telecel-cash' => ['name' => 'Telecel Cash', 'type' => 'mobile'],
        'airtel-gh' => ['name' => 'AirtelTigo Money Ghana', 'type' => 'mobile'],
        'zeepay' => ['name' => 'Zeepay', 'type' => 'api'],
        'hubtel' => ['name' => 'Hubtel', 'type' => 'api'],
        'ghipps' => ['name' => 'GHIPPS', 'type' => 'api'],
        'ecobank-gh' => ['name' => 'Ecobank Ghana', 'type' => 'bank'],
        'stanbic-gh' => ['name' => 'Stanbic Ghana', 'type' => 'bank'],
        'paystack-gh' => ['name' => 'Paystack Ghana', 'type' => 'api'],
        'wo-pay' => ['name' => 'WoPay', 'type' => 'api'],
    ],

    'GR' => [
        'viva-gr' => ['name' => 'Viva Wallet', 'type' => 'api'],
        'paycenter' => ['name' => 'PayCenter', 'type' => 'api'],
        'national-bank-gr' => ['name' => 'National Bank of Greece', 'type' => 'bank'],
        'piraeus' => ['name' => 'Piraeus Bank', 'type' => 'bank'],
        'alpha-bank' => ['name' => 'Alpha Bank', 'type' => 'bank'],
        'eurobank-gr' => ['name' => 'Eurobank', 'type' => 'bank'],
        'irispayments' => ['name' => 'IRIS Payments', 'type' => 'api'],
    ],

    'GD' => [
        'rbc-gd' => ['name' => 'RBC Grenada', 'type' => 'bank'],
        'gnb' => ['name' => 'Grenada National Bank', 'type' => 'bank'],
        'firstcaribbean-gd' => ['name' => 'CIBC FirstCaribbean Grenada', 'type' => 'bank'],
        'grenada-pay' => ['name' => 'Grenada Pay', 'type' => 'api'],
    ],

    'GT' => [
        'bac-gt' => ['name' => 'BAC Credomatic Guatemala', 'type' => 'api'],
        'banco-industrial-gt' => ['name' => 'Banco Industrial', 'type' => 'bank'],
        'gye-gt' => ['name' => 'GYE', 'type' => 'bank'],
        'banrural-gt' => ['name' => 'Banrural', 'type' => 'bank'],
        'pagos-gt' => ['name' => 'Pagos GT', 'type' => 'api'],
        'pays-gt' => ['name' => 'Pay Guatemala', 'type' => 'api'],
        'tigo-money-gt' => ['name' => 'Tigo Money Guatemala', 'type' => 'mobile'],
    ],

    'GN' => [
        'orange-gn' => ['name' => 'Orange Money Guinea', 'type' => 'mobile'],
        'mtn-gn' => ['name' => 'MTN MoMo Guinea', 'type' => 'mobile'],
        'yami-money' => ['name' => 'Yami Money', 'type' => 'mobile'],
        'bbg' => ['name' => 'BBG', 'type' => 'bank'],
        'ecobank-gn' => ['name' => 'Ecobank Guinea', 'type' => 'bank'],
    ],

    'GW' => [
        'orange-gw' => ['name' => 'Orange Money Guinea-Bissau', 'type' => 'mobile'],
        'mtn-gw' => ['name' => 'MTN MoMo Guinea-Bissau', 'type' => 'mobile'],
        'bdu-gw' => ['name' => 'BDU', 'type' => 'bank'],
        'guibiss-pay' => ['name' => 'GuiBiss Pay', 'type' => 'api'],
    ],

    'GY' => [
        'gbti' => ['name' => 'Guyana Bank for Trade & Industry', 'type' => 'bank'],
        'citizen-bank-gy' => ['name' => 'Citizens Bank Guyana', 'type' => 'bank'],
        'rep-bank-gy' => ['name' => 'Republic Bank Guyana', 'type' => 'bank'],
        'guy-pay' => ['name' => 'GuyPay', 'type' => 'mobile'],
    ],

    'HT' => [
        'natcash-ht' => ['name' => 'Natcash', 'type' => 'mobile'],
        'moncash' => ['name' => 'MonCash', 'type' => 'mobile'],
        'sogebank' => ['name' => 'Sogebank', 'type' => 'bank'],
        'banj' => ['name' => 'BANJ', 'type' => 'bank'],
        'hapipay' => ['name' => 'HapiPay', 'type' => 'api'],
    ],

    'HN' => [
        'bac-hn' => ['name' => 'BAC Credomatic Honduras', 'type' => 'api'],
        'banpais' => ['name' => 'BanPais', 'type' => 'bank'],
        'bga' => ['name' => 'Banco de Occidente', 'type' => 'bank'],
        'davivienda-hn' => ['name' => 'Davivienda Honduras', 'type' => 'bank'],
        'tigo-money-hn' => ['name' => 'Tigo Money Honduras', 'type' => 'mobile'],
        'hon-pay' => ['name' => 'HonPay', 'type' => 'api'],
    ],

    'HU' => [
        'otp-pay-hr' => ['name' => 'OTP Pay', 'type' => 'api'],
        'payu-hu' => ['name' => 'PayU Hungary', 'type' => 'api'],
        'otp-bank' => ['name' => 'OTP Bank', 'type' => 'bank'],
        'k&h' => ['name' => 'K&H Bank', 'type' => 'bank'],
        'szep' => ['name' => 'SZÉP Card', 'type' => 'api'],
        'simplepay' => ['name' => 'SimplePay', 'type' => 'api'],
        'barion' => ['name' => 'Barion', 'type' => 'api'],
    ],

    'IS' => [
        'borgun-is' => ['name' => 'Borgun', 'type' => 'api'],
        'valitor-is' => ['name' => 'Valitor', 'type' => 'api'],
        'landsbankinn' => ['name' => 'Landsbankinn', 'type' => 'bank'],
        'arion' => ['name' => 'Arion Bank', 'type' => 'bank'],
        'islandsbanki' => ['name' => 'Íslandsbanki', 'type' => 'bank'],
        'netpay-is' => ['name' => 'NetPay', 'type' => 'api'],
    ],

    'IN' => [
        'razorpay-in' => ['name' => 'Razorpay', 'type' => 'api', 'driver' => 'Hadi\\Payment\\Gateways\\RazorpayGateway'],
        'paytm-in-local' => ['name' => 'Paytm', 'type' => 'api', 'driver' => 'Hadi\\Payment\\Gateways\\PaytmGateway'],
        'phonepe-in' => ['name' => 'PhonePe', 'type' => 'api', 'driver' => 'Hadi\\Payment\\Gateways\\PhonePeGateway'],
        'upi-in' => ['name' => 'UPI', 'type' => 'bank'],
        'payumoney-in' => ['name' => 'PayUmoney', 'type' => 'api'],
        'billdesk-in' => ['name' => 'BillDesk', 'type' => 'api'],
        'ccavenue-in' => ['name' => 'CCAvenue', 'type' => 'api'],
        'payu-in-local' => ['name' => 'PayU India', 'type' => 'api', 'driver' => 'Hadi\\Payment\\Gateways\\PayUGateway'],
        'cashfree-in' => ['name' => 'Cashfree', 'type' => 'api'],
        'instamojo-in' => ['name' => 'Instamojo', 'type' => 'api'],
        'airpay-in' => ['name' => 'Airpay', 'type' => 'api'],
        'yesbank-in' => ['name' => 'Yes Bank', 'type' => 'bank'],
        'hdfc-in' => ['name' => 'HDFC Bank', 'type' => 'bank'],
        'icici-in' => ['name' => 'ICICI Bank', 'type' => 'bank'],
        'sbipay-in' => ['name' => 'SBI Pay', 'type' => 'bank'],
        'jupiter' => ['name' => 'Jupiter', 'type' => 'wallet'],
        'cred' => ['name' => 'CRED', 'type' => 'wallet'],
        'payoneer-in' => ['name' => 'Payoneer India', 'type' => 'wallet'],
        'upi-app' => ['name' => 'UPI Apps', 'type' => 'bank'],
        'nfpl' => ['name' => 'NFPL', 'type' => 'api'],
    ],

    'ID' => [
        'midtrans-id' => ['name' => 'Midtrans', 'type' => 'api'],
        'xendit-id' => ['name' => 'Xendit', 'type' => 'api'],
        'ovo-id' => ['name' => 'OVO', 'type' => 'wallet'],
        'dana-id' => ['name' => 'DANA', 'type' => 'wallet'],
        'gopay-id' => ['name' => 'GoPay', 'type' => 'wallet'],
        'shopee-id' => ['name' => 'ShopeePay', 'type' => 'wallet'],
        'bca-id' => ['name' => 'BCA', 'type' => 'bank'],
        'bni-id' => ['name' => 'BNI', 'type' => 'bank'],
        'mandiri' => ['name' => 'Mandiri', 'type' => 'bank'],
        'indomaret' => ['name' => 'Indomaret', 'type' => 'api'],
        'alfamart' => ['name' => 'Alfamart', 'type' => 'api'],
        'kredivo' => ['name' => 'Kredivo', 'type' => 'api'],
        'akulaku' => ['name' => 'Akulaku', 'type' => 'api'],
        'jenius' => ['name' => 'Jenius', 'type' => 'wallet'],
    ],

    'IR' => [
        'zarinpal-ir' => ['name' => 'Zarinpal', 'type' => 'api'],
        'idpay-ir' => ['name' => 'IDPay', 'type' => 'api'],
        'payping-ir' => ['name' => 'Payping', 'type' => 'api'],
        'behpardakht' => ['name' => 'Behpardakht Mellat', 'type' => 'api'],
        'samanbank' => ['name' => 'Saman Bank', 'type' => 'api'],
        'sadad-ir' => ['name' => 'SADAD Iran', 'type' => 'api'],
        'parsian' => ['name' => 'Parsian Bank', 'type' => 'api'],
        'asan-pardakht' => ['name' => 'Asan Pardakht', 'type' => 'api'],
        'pasargad' => ['name' => 'Pasargad', 'type' => 'api'],
        'saderat' => ['name' => 'Saderat', 'type' => 'api'],
        'nextpay' => ['name' => 'NextPay', 'type' => 'api'],
        'digipay' => ['name' => 'DigiPay', 'type' => 'api'],
    ],

    'IQ' => [
        'zain-iq' => ['name' => 'Zain Cash Iraq', 'type' => 'mobile'],
        'asia-cell' => ['name' => 'Asia Cell', 'type' => 'mobile'],
        'fastlink' => ['name' => 'FastLink', 'type' => 'mobile'],
        'cbi-iq' => ['name' => 'CBI Iraq', 'type' => 'bank'],
        'raffidain' => ['name' => 'Rafidain Bank', 'type' => 'bank'],
        'rai' => ['name' => 'Rasheed Bank', 'type' => 'bank'],
        'iq-pay' => ['name' => 'IQ Pay', 'type' => 'api'],
    ],

    'IE' => [
        'realex-ie' => ['name' => 'Realex', 'type' => 'api'],
        'stripe-ie' => ['name' => 'Stripe Ireland', 'type' => 'api'],
        'aib' => ['name' => 'AIB', 'type' => 'bank'],
        'bofie' => ['name' => 'Bank of Ireland', 'type' => 'bank'],
        'kbc-ie' => ['name' => 'KBC Ireland', 'type' => 'bank'],
        'permanent-tsb' => ['name' => 'Permanent TSB', 'type' => 'bank'],
        'firepay' => ['name' => 'Firepay', 'type' => 'api'],
    ],

    'IL' => [
        'transillia' => ['name' => 'Trans-Ilia', 'type' => 'api'],
        'pelecard-il' => ['name' => 'Pelecard', 'type' => 'api'],
        'isracard' => ['name' => 'Isracard', 'type' => 'api'],
        'cal' => ['name' => 'CAL', 'type' => 'api'],
        'max-il' => ['name' => 'Max', 'type' => 'api'],
        'bank-hapoalim' => ['name' => 'Bank Hapoalim', 'type' => 'bank'],
        'bank-leumi' => ['name' => 'Bank Leumi', 'type' => 'bank'],
        'bit-il' => ['name' => 'Bit', 'type' => 'wallet'],
        'googlepay-il' => ['name' => 'Google Pay Israel', 'type' => 'wallet'],
    ],

    'IT' => [
        'nexi-it' => ['name' => 'Nexi', 'type' => 'api'],
        'postepay-it' => ['name' => 'PostePay', 'type' => 'api'],
        'satispay-it' => ['name' => 'Satispay', 'type' => 'wallet'],
        'intesa' => ['name' => 'Intesa Sanpaolo', 'type' => 'bank'],
        'unicredit-it' => ['name' => 'UniCredit', 'type' => 'bank'],
        'banca-sella-it' => ['name' => 'Banca Sella', 'type' => 'api'],
        'bancoposta' => ['name' => 'BancoPosta', 'type' => 'bank'],
        'pago-pa' => ['name' => 'PagoPA', 'type' => 'api'],
        'checkout-it' => ['name' => 'Checkout Italia', 'type' => 'api'],
    ],

    'JM' => [
        'jam-cash' => ['name' => 'Jamaica Money', 'type' => 'api'],
        'ncbt' => ['name' => 'NCB', 'type' => 'bank'],
        'jam-bank' => ['name' => 'Bank of Jamaica', 'type' => 'bank'],
        'scotiabank-jm' => ['name' => 'Scotiabank Jamaica', 'type' => 'bank'],
        'jtb' => ['name' => 'Jamaica Broilers', 'type' => 'api'],
        'epci' => ['name' => 'EPCI', 'type' => 'api'],
    ],

    'JP' => [
        'konbini-jp' => ['name' => 'Konbini', 'type' => 'bank'],
        'paypay-jp' => ['name' => 'PayPay', 'type' => 'wallet'],
        'rakuten-pay-jp' => ['name' => 'Rakuten Pay', 'type' => 'wallet'],
        'line-pay-jp' => ['name' => 'LINE Pay', 'type' => 'wallet'],
        'docomokoraku-jp' => ['name' => 'D Payment', 'type' => 'wallet'],
        'au-pay-jp' => ['name' => 'au PAY', 'type' => 'wallet'],
        'gmo-jp' => ['name' => 'GMO PG', 'type' => 'api'],
        'veritrans-jp' => ['name' => 'VeriTrans', 'type' => 'api'],
        'sony-pay' => ['name' => 'Sony Pay', 'type' => 'wallet'],
        'mizuho' => ['name' => 'Mizuho Bank', 'type' => 'bank'],
        'mufg' => ['name' => 'MUFG', 'type' => 'bank'],
        'smbc' => ['name' => 'SMBC', 'type' => 'bank'],
        'paidy' => ['name' => 'Paidy', 'type' => 'api'],
        'atone' => ['name' => 'Atone', 'type' => 'api'],
        'webmoney-jp' => ['name' => 'WebMoney Japan', 'type' => 'wallet'],
    ],

    'JO' => [
        'jo-pay' => ['name' => 'JoPay', 'type' => 'api'],
        'arabi-bank-jo' => ['name' => 'Arab Bank Jordan', 'type' => 'bank'],
        'ahli-jo' => ['name' => 'Ahli Bank', 'type' => 'bank'],
        'bank-al-etihad' => ['name' => 'Bank Al Etihad', 'type' => 'bank'],
        'zain-jo' => ['name' => 'Zain Cash Jordan', 'type' => 'mobile'],
        'orange-jo' => ['name' => 'Orange Money Jordan', 'type' => 'mobile'],
        'madfooatcom' => ['name' => 'MadfooatCom', 'type' => 'api'],
    ],

    'KZ' => [
        'kaspi-kz' => ['name' => 'Kaspi', 'type' => 'api'],
        'halyk-kz' => ['name' => 'Halyk Bank', 'type' => 'api'],
        'fortebank' => ['name' => 'FortéBank', 'type' => 'bank'],
        'bank-center-credit' => ['name' => 'Bank CenterCredit', 'type' => 'bank'],
        'kcell' => ['name' => 'Kcell', 'type' => 'mobile'],
        'beeline-kz' => ['name' => 'Beeline Kazakhstan', 'type' => 'mobile'],
        'paykz' => ['name' => 'PayKZ', 'type' => 'api'],
    ],

    'KE' => [
        'm-pesa-ke-local' => ['name' => 'M-Pesa Kenya', 'type' => 'mobile', 'driver' => 'Hadi\\Payment\\Gateways\\MpesaGateway'],
        'airtel-ke-local' => ['name' => 'Airtel Money Kenya', 'type' => 'mobile'],
        'telkom-ke' => ['name' => 'Telkom Kenya', 'type' => 'mobile'],
        'pesalink-ke' => ['name' => 'PesaLink', 'type' => 'bank'],
        'lipa-ke' => ['name' => 'Lipa Na M-Pesa', 'type' => 'mobile'],
        'equity-ke' => ['name' => 'Equity Bank', 'type' => 'bank'],
        'kcb-ke' => ['name' => 'KCB Bank', 'type' => 'bank'],
        'coop-ke' => ['name' => 'Co-operative Bank', 'type' => 'bank'],
        'cellulant-ke' => ['name' => 'Cellulant', 'type' => 'api'],
        'pesapal-ke' => ['name' => 'PesaPal', 'type' => 'api'],
        'sokowatch' => ['name' => 'SokoWatch', 'type' => 'api'],
    ],

    'KI' => [
        'bank-of-kiribati' => ['name' => 'Bank of Kiribati', 'type' => 'bank'],
        'kfcu' => ['name' => 'KFCU', 'type' => 'bank'],
        'anz-ki' => ['name' => 'ANZ Kiribati', 'type' => 'bank'],
        'kir-pay' => ['name' => 'KirPay', 'type' => 'api'],
    ],

    'KP' => [
        'korea-bank-np' => ['name' => 'Korea Bank', 'type' => 'bank'],
        'chongjin' => ['name' => 'Chongjin Bank', 'type' => 'bank'],
        'kdab' => ['name' => 'KDAB', 'type' => 'bank'],
    ],

    'KR' => [
        'kakao-pay-kr' => ['name' => 'KakaoPay', 'type' => 'wallet'],
        'naver-pay-kr' => ['name' => 'Naver Pay', 'type' => 'wallet'],
        'toss-kr' => ['name' => 'Toss', 'type' => 'api'],
        'kg-inicis-kr' => ['name' => 'KG Inicis', 'type' => 'api'],
        'nhn-kcp-kr' => ['name' => 'NHN KCP', 'type' => 'api'],
        'npay' => ['name' => 'NPay', 'type' => 'wallet'],
        'payco' => ['name' => 'Payco', 'type' => 'wallet'],
        'samsung-pay-kr' => ['name' => 'Samsung Pay Korea', 'type' => 'wallet'],
        'shinhan' => ['name' => 'Shinhan Bank', 'type' => 'bank'],
        'kb-kr' => ['name' => 'KB Kookmin', 'type' => 'bank'],
        'hana' => ['name' => 'Hana Bank', 'type' => 'bank'],
        'woori' => ['name' => 'Woori Bank', 'type' => 'bank'],
    ],

    'KW' => [
        'knet-kw' => ['name' => 'KNET', 'type' => 'api'],
        'nbk-kw' => ['name' => 'NBK', 'type' => 'bank'],
        'gulf-bank' => ['name' => 'Gulf Bank', 'type' => 'bank'],
        'kuwait-international' => ['name' => 'Kuwait International Bank', 'type' => 'bank'],
        'zain-kw' => ['name' => 'Zain Cash Kuwait', 'type' => 'mobile'],
        'stc-kw' => ['name' => 'STC Pay Kuwait', 'type' => 'mobile'],
        'boubyan' => ['name' => 'Boubyan Bank', 'type' => 'bank'],
    ],

    'KG' => [
        'mega-pay-kg' => ['name' => 'MegaPay', 'type' => 'api'],
        'elcard' => ['name' => 'Elcard', 'type' => 'api'],
        'kyrgyz-bank' => ['name' => 'Kyrgyz Bank', 'type' => 'bank'],
        'rsb' => ['name' => 'RSK Bank', 'type' => 'bank'],
        'moyo-kg' => ['name' => 'MoYo', 'type' => 'mobile'],
        'osoom' => ['name' => 'Osoon', 'type' => 'api'],
    ],

    'LA' => [
        'lcr-la' => ['name' => 'Lao Central Bank QR', 'type' => 'api'],
        'bcel' => ['name' => 'BCEL', 'type' => 'bank'],
        'lane-xang' => ['name' => 'Lane Xang', 'type' => 'bank'],
        'lao-tel' => ['name' => 'Lao Telecom', 'type' => 'mobile'],
        'unitel-la' => ['name' => 'Unitel Laos', 'type' => 'mobile'],
    ],

    'LV' => [
        'maksekeskus-lv' => ['name' => 'Maksekeskus Latvia', 'type' => 'api'],
        'swedbank-lv' => ['name' => 'Swedbank Latvia', 'type' => 'bank'],
        'seb-lv' => ['name' => 'SEB Latvia', 'type' => 'bank'],
        'citadele' => ['name' => 'Citadele', 'type' => 'bank'],
        'luminor' => ['name' => 'Luminor', 'type' => 'bank'],
        'firstdata-lv' => ['name' => 'First Data Latvia', 'type' => 'api'],
    ],

    'LB' => [
        'omt-lb' => ['name' => 'OMT', 'type' => 'api'],
        'whish-lb' => ['name' => 'Whish Money', 'type' => 'api'],
        'blom' => ['name' => 'Blom Bank', 'type' => 'bank'],
        'bankaudi' => ['name' => 'Bank Audi', 'type' => 'bank'],
        'byblos' => ['name' => 'Byblos Bank', 'type' => 'bank'],
        'touch-lb' => ['name' => 'Touch', 'type' => 'mobile'],
        'almtared' => ['name' => 'Al Moutaref', 'type' => 'api'],
    ],

    'LS' => [
        'vodacom-ls' => ['name' => 'Vodacom Lesotho', 'type' => 'mobile'],
        'econet-ls' => ['name' => 'Econet Lesotho', 'type' => 'mobile'],
        'lbl' => ['name' => 'Lesotho Bank', 'type' => 'bank'],
        'nlb' => ['name' => 'Nedbank Lesotho', 'type' => 'bank'],
    ],

    'LR' => [
        'lone-star' => ['name' => 'Lone Star Cell', 'type' => 'mobile'],
        'orange-lr' => ['name' => 'Orange Money Liberia', 'type' => 'mobile'],
        'lbd' => ['name' => 'Liberian Bank for Development', 'type' => 'bank'],
        'ecobank-lr' => ['name' => 'Ecobank Liberia', 'type' => 'bank'],
    ],

    'LY' => [
        'al-aman' => ['name' => 'Al Aman', 'type' => 'mobile'],
        'sadaraf' => ['name' => 'Sadara Bank', 'type' => 'bank'],
        'libya-pay' => ['name' => 'Libya Pay', 'type' => 'api'],
        'jib' => ['name' => 'JIB', 'type' => 'bank'],
        'al-wahda' => ['name' => 'Al Wahda Bank', 'type' => 'bank'],
    ],

    'LI' => [
        'lgt' => ['name' => 'LGT', 'type' => 'bank'],
        'llb' => ['name' => 'Liechtensteinische Landesbank', 'type' => 'bank'],
        'vp-bank' => ['name' => 'VP Bank', 'type' => 'bank'],
        'frick' => ['name' => 'Bank Frick', 'type' => 'bank'],
    ],

    'LT' => [
        'paysera-lt' => ['name' => 'Paysera', 'type' => 'api'],
        'swedbank-lt' => ['name' => 'Swedbank Lithuania', 'type' => 'bank'],
        'seb-lt' => ['name' => 'SEB Lithuania', 'type' => 'bank'],
        'luminor-lt' => ['name' => 'Luminor Lithuania', 'type' => 'bank'],
        'eurokon' => ['name' => 'Eurokon', 'type' => 'api'],
        'sila' => ['name' => 'Sila Payments', 'type' => 'api'],
    ],

    'LU' => [
        'bcee' => ['name' => 'BCEE', 'type' => 'bank'],
        'bgl' => ['name' => 'BGL BNP Paribas', 'type' => 'bank'],
        'banque-internationale' => ['name' => 'Banque Internationale à Luxembourg', 'type' => 'bank'],
        'digicash' => ['name' => 'Digicash', 'type' => 'api'],
        'payconiq-lu' => ['name' => 'Payconiq Luxembourg', 'type' => 'wallet'],
    ],

    'MG' => [
        'mvola-mg' => ['name' => 'MVola', 'type' => 'mobile'],
        'airtel-mg' => ['name' => 'Airtel Money Madagascar', 'type' => 'mobile'],
        'telma-mg' => ['name' => 'Telma', 'type' => 'mobile'],
        'bfv' => ['name' => 'BFV', 'type' => 'bank'],
        'bnm-mg' => ['name' => 'BNI Madagascar', 'type' => 'bank'],
        'vola' => ['name' => 'Vola', 'type' => 'mobile'],
    ],

    'MW' => [
        'tnm-mpamba-mw' => ['name' => 'TNM Mpamba', 'type' => 'mobile'],
        'airtel-mw' => ['name' => 'Airtel Money Malawi', 'type' => 'mobile'],
        'nbs-bank' => ['name' => 'NBS Bank', 'type' => 'bank'],
        'standard-bank-mw' => ['name' => 'Standard Bank Malawi', 'type' => 'bank'],
        'fdh' => ['name' => 'FDH Bank', 'type' => 'bank'],
    ],

    'MY' => [
        'fpx-my' => ['name' => 'FPX', 'type' => 'bank'],
        'touchngo-my' => ['name' => 'Touch n Go eWallet', 'type' => 'wallet'],
        'boost-my' => ['name' => 'Boost', 'type' => 'wallet'],
        'grabpay-my' => ['name' => 'GrabPay Malaysia', 'type' => 'wallet'],
        'shopee-my' => ['name' => 'ShopeePay Malaysia', 'type' => 'wallet'],
        'ipay88-my' => ['name' => 'iPay88', 'type' => 'api'],
        'billplz-my' => ['name' => 'Billplz', 'type' => 'api'],
        'maybank2u' => ['name' => 'Maybank2u', 'type' => 'bank'],
        'cimb-clicks' => ['name' => 'CIMB Clicks', 'type' => 'bank'],
        'razorpay-my' => ['name' => 'Razorpay Malaysia', 'type' => 'api'],
    ],

    'MV' => [
        'maldives-islamic' => ['name' => 'Maldives Islamic Bank', 'type' => 'bank'],
        'bml' => ['name' => 'Bank of Maldives', 'type' => 'bank'],
        'm-paisa-mv' => ['name' => 'M-Paisa Maldives', 'type' => 'mobile'],
        'ooredoo-mv' => ['name' => 'Ooredoo Maldives', 'type' => 'mobile'],
        'dhillamal' => ['name' => 'DhillaMal', 'type' => 'api'],
    ],

    'ML' => [
        'orange-ml-cash' => ['name' => 'Orange Money Mali', 'type' => 'mobile'],
        'mtn-ml' => ['name' => 'MTN MoMo Mali', 'type' => 'mobile'],
        'moov-ml' => ['name' => 'Moov Money Mali', 'type' => 'mobile'],
        'bndaml' => ['name' => 'BNDA', 'type' => 'bank'],
        'ecobank-ml' => ['name' => 'Ecobank Mali', 'type' => 'bank'],
    ],

    'MT' => [
        'aps-mt' => ['name' => 'APS Bank', 'type' => 'bank'],
        'hsbc-mt' => ['name' => 'HSBC Malta', 'type' => 'bank'],
        'bov' => ['name' => 'Bank of Valletta', 'type' => 'bank'],
        'lombard' => ['name' => 'Lombard Bank', 'type' => 'bank'],
        'maltapay' => ['name' => 'MaltaPay', 'type' => 'api'],
    ],

    'MH' => [
        'bok' => ['name' => 'Bank of Marshall Islands', 'type' => 'bank'],
        'bankofguam' => ['name' => 'Bank of Guam', 'type' => 'bank'],
        'mhc' => ['name' => 'Marshall Islands Credit', 'type' => 'bank'],
    ],

    'MR' => [
        'masrvi-mr' => ['name' => 'Masrvi', 'type' => 'mobile'],
        'chinguitti' => ['name' => 'Chinguitti', 'type' => 'mobile'],
        'bmci' => ['name' => 'BMCI', 'type' => 'bank'],
        'bcm-mr' => ['name' => 'BCM Mauritania', 'type' => 'bank'],
        'mattel' => ['name' => 'Mattel', 'type' => 'api'],
    ],

    'MU' => [
        'mcash-mu' => ['name' => 'MCB Juice', 'type' => 'mobile'],
        'myb-mobile-mu' => ['name' => 'MyB Mobile', 'type' => 'mobile'],
        'bl-mu' => ['name' => 'Bank of Mauritius', 'type' => 'bank'],
        'mcb-mu' => ['name' => 'MCB', 'type' => 'bank'],
        'absa-mu' => ['name' => 'Absa Mauritius', 'type' => 'bank'],
    ],

    'MX' => [
        'oxxo-mx' => ['name' => 'OXXO', 'type' => 'bank'],
        'spei' => ['name' => 'SPEI', 'type' => 'bank'],
        'clip-mx' => ['name' => 'Clip', 'type' => 'api'],
        'conekta-mx' => ['name' => 'Conekta', 'type' => 'api'],
        'openpay-mx' => ['name' => 'Openpay', 'type' => 'api'],
        'paypal-mx' => ['name' => 'PayPal Mexico', 'type' => 'api'],
        'bbva-mx' => ['name' => 'BBVA Mexico', 'type' => 'bank'],
        'banorte' => ['name' => 'Banorte', 'type' => 'bank'],
        'coppel' => ['name' => 'Coppel Pay', 'type' => 'api'],
        'dito' => ['name' => 'Dito', 'type' => 'api'],
        'mercadopago-mx' => ['name' => 'Mercado Pago Mexico', 'type' => 'api'],
        'dimmo' => ['name' => 'Dimo', 'type' => 'mobile'],
    ],

    'FM' => [
        'bank-of-fsm' => ['name' => 'Bank of the Federated States of Micronesia', 'type' => 'bank'],
        'fsm-telecom' => ['name' => 'FSM Telecom', 'type' => 'mobile'],
        'fmb' => ['name' => 'FMB', 'type' => 'bank'],
    ],

    'MD' => [
        'paynet-md' => ['name' => 'Paynet', 'type' => 'api'],
        'maib' => ['name' => 'MAIB', 'type' => 'bank'],
        'moldindconbank' => ['name' => 'Moldindconbank', 'type' => 'bank'],
        'victoria-bank' => ['name' => 'Victoria Bank', 'type' => 'bank'],
        'moldova-pay' => ['name' => 'MoldovaPay', 'type' => 'api'],
    ],

    'MC' => [
        'bcm-mc' => ['name' => 'BCM Monaco', 'type' => 'bank'],
        'cfm-mc' => ['name' => 'CFM Monaco', 'type' => 'bank'],
        'sbm' => ['name' => 'SBM Monaco', 'type' => 'bank'],
        'monaco-pay' => ['name' => 'Monaco Pay', 'type' => 'api'],
    ],

    'MN' => [
        'golomt' => ['name' => 'Golomt Bank', 'type' => 'bank'],
        'khan-bank' => ['name' => 'Khan Bank', 'type' => 'bank'],
        'tdb' => ['name' => 'Trade & Development Bank', 'type' => 'bank'],
        'mobicom' => ['name' => 'Mobicom', 'type' => 'mobile'],
        'unitel-mn' => ['name' => 'Unitel Mongolia', 'type' => 'mobile'],
        'qpay-mn' => ['name' => 'QPay', 'type' => 'api'],
    ],

    'ME' => [
        'cbm-me' => ['name' => 'Crnogorska Banka', 'type' => 'bank'],
        'podgoricka' => ['name' => 'Podgorička Banka', 'type' => 'bank'],
        'adriatic' => ['name' => 'Adriatic Bank', 'type' => 'bank'],
        'me-pay' => ['name' => 'Montenegro Pay', 'type' => 'api'],
    ],

    'MA' => [
        'cmi-ma' => ['name' => 'CMI', 'type' => 'api'],
        'matta' => ['name' => 'Matta', 'type' => 'api'],
        'imtiaz' => ['name' => 'Imtiaz', 'type' => 'api'],
        'attijariwafa' => ['name' => 'Attijariwafa Bank', 'type' => 'bank'],
        'bmce' => ['name' => 'BMCE', 'type' => 'bank'],
        'cih' => ['name' => 'CIH Bank', 'type' => 'bank'],
        'inwi' => ['name' => 'inwi Money', 'type' => 'mobile'],
        'maroc-telecom' => ['name' => 'Maroc Telecom', 'type' => 'mobile'],
        'leocash' => ['name' => 'LeoCash', 'type' => 'api'],
        'pay2go' => ['name' => 'Pay2Go', 'type' => 'api'],
    ],

    'MZ' => [
        'm-pesa-mz-local' => ['name' => 'M-Pesa Mozambique', 'type' => 'mobile'],
        'emola-mz' => ['name' => 'e-Mola', 'type' => 'mobile'],
        'unitel-mz' => ['name' => 'Unitel', 'type' => 'mobile'],
        'vodacom-mz' => ['name' => 'Vodacom Mozambique', 'type' => 'mobile'],
        'bim' => ['name' => 'BIM', 'type' => 'bank'],
        'standard-bank-mz' => ['name' => 'Standard Bank Mozambique', 'type' => 'bank'],
        'mcash-mz' => ['name' => 'MCash', 'type' => 'mobile'],
    ],

    'MM' => [
        'kbz-pay-mm' => ['name' => 'KBZ Pay', 'type' => 'mobile'],
        'wave-money-mm' => ['name' => 'Wave Money', 'type' => 'mobile'],
        'true-money-mm' => ['name' => 'TrueMoney Myanmar', 'type' => 'wallet'],
        'my-pay-mm' => ['name' => 'MyPay', 'type' => 'mobile'],
        'cb-mm' => ['name' => 'CB Bank', 'type' => 'bank'],
        'kbz-bank' => ['name' => 'KBZ Bank', 'type' => 'bank'],
        'aya-bank' => ['name' => 'AYA Bank', 'type' => 'bank'],
    ],

    'NA' => [
        'm-pesa-na' => ['name' => 'M-Pesa Namibia', 'type' => 'mobile'],
        'fnb-namibia' => ['name' => 'FNB Namibia', 'type' => 'bank'],
        'bank-windhoek' => ['name' => 'Bank Windhoek', 'type' => 'bank'],
        'nedbank-na' => ['name' => 'Nedbank Namibia', 'type' => 'bank'],
        'nam-pay' => ['name' => 'NamPay', 'type' => 'api'],
    ],

    'NR' => [
        'bnr' => ['name' => 'Bank of Nauru', 'type' => 'bank'],
        'nauru-tel' => ['name' => 'Nauru Telecom', 'type' => 'mobile'],
        'nauru-pay' => ['name' => 'NauruPay', 'type' => 'api'],
    ],

    'NP' => [
        'esewa-np' => ['name' => 'eSewa', 'type' => 'wallet'],
        'khalti-np' => ['name' => 'Khalti', 'type' => 'api'],
        'ime-pay-np' => ['name' => 'IME Pay', 'type' => 'mobile'],
        'connectips-np' => ['name' => 'Connect IPS', 'type' => 'api'],
        'fonepay-np' => ['name' => 'FonePay', 'type' => 'mobile'],
        'prabhu-pay' => ['name' => 'PrabhuPay', 'type' => 'api'],
        'nic-asia' => ['name' => 'NIC Asia', 'type' => 'bank'],
        'nrb' => ['name' => 'Nepal Rastra Bank', 'type' => 'bank'],
        'ncell' => ['name' => 'Ncell', 'type' => 'mobile'],
        'nepal-pay' => ['name' => 'NepalPay', 'type' => 'api'],
    ],

    'NL' => [
        'ideal-nl' => ['name' => 'iDEAL', 'type' => 'bank'],
        'mollie-nl' => ['name' => 'Mollie', 'type' => 'api'],
        'adyen-nl' => ['name' => 'Adyen Netherlands', 'type' => 'api'],
        'buckaroo-nl' => ['name' => 'Buckaroo', 'type' => 'api'],
        'rabo' => ['name' => 'Rabobank', 'type' => 'bank'],
        'ing-nl' => ['name' => 'ING Netherlands', 'type' => 'bank'],
        'abn-amro' => ['name' => 'ABN AMRO', 'type' => 'bank'],
        'tikkie' => ['name' => 'Tikkie', 'type' => 'wallet'],
        'payt-nl' => ['name' => 'PAYT', 'type' => 'api'],
        'multisafepay' => ['name' => 'MultiSafepay', 'type' => 'api'],
    ],

    'NZ' => [
        'paymark-nz' => ['name' => 'Paymark', 'type' => 'api'],
        'polipayments-nz' => ['name' => 'POLi', 'type' => 'bank'],
        'paymentexpress-nz' => ['name' => 'Payment Express', 'type' => 'api'],
        'anz-nz' => ['name' => 'ANZ New Zealand', 'type' => 'bank'],
        'bnz' => ['name' => 'BNZ', 'type' => 'bank'],
        'westpac-nz' => ['name' => 'Westpac NZ', 'type' => 'bank'],
        'asb' => ['name' => 'ASB', 'type' => 'bank'],
        'latipay-nz' => ['name' => 'LatiPay', 'type' => 'api'],
    ],

    'NI' => [
        'bac-ni' => ['name' => 'BAC Credomatic Nicaragua', 'type' => 'api'],
        'banpro' => ['name' => 'Banpro', 'type' => 'bank'],
        'bcn' => ['name' => 'BCN', 'type' => 'bank'],
        'tigo-money-ni' => ['name' => 'Tigo Money Nicaragua', 'type' => 'mobile'],
        'claro-ni' => ['name' => 'Claro Nicaragua', 'type' => 'mobile'],
        'nic-pay' => ['name' => 'NicPay', 'type' => 'api'],
    ],

    'NE' => [
        'orange-ne' => ['name' => 'Orange Money Niger', 'type' => 'mobile'],
        'moov-ne' => ['name' => 'Moov Money Niger', 'type' => 'mobile'],
        'sonatel-ne' => ['name' => 'Sonatel Niger', 'type' => 'mobile'],
        'bnd-niger' => ['name' => 'BND', 'type' => 'bank'],
        'ecobank-ne' => ['name' => 'Ecobank Niger', 'type' => 'bank'],
    ],

    'NG' => [
        'paystack-ng-local' => ['name' => 'Paystack Nigeria', 'type' => 'api', 'driver' => 'Hadi\\Payment\\Gateways\\PaystackGateway'],
        'flutterwave-ng-local' => ['name' => 'Flutterwave Nigeria', 'type' => 'api', 'driver' => 'Hadi\\Payment\\Gateways\\FlutterwaveGateway'],
        'opay-ng' => ['name' => 'OPay', 'type' => 'wallet'],
        'palmpay-ng' => ['name' => 'PalmPay', 'type' => 'wallet'],
        'moniepoint-ng' => ['name' => 'Moniepoint', 'type' => 'api'],
        'korapay-ng' => ['name' => 'Kora Pay', 'type' => 'api'],
        'providus' => ['name' => 'Providus', 'type' => 'api'],
        'interswitch-ng' => ['name' => 'Interswitch Nigeria', 'type' => 'api'],
        'verve-ng' => ['name' => 'Verve', 'type' => 'card'],
        'gtbank-ng' => ['name' => 'GTBank', 'type' => 'bank'],
        'zenith-ng' => ['name' => 'Zenith Bank', 'type' => 'bank'],
        'access-ng' => ['name' => 'Access Bank', 'type' => 'bank'],
        'uba-ng' => ['name' => 'UBA', 'type' => 'bank'],
        'firstbank-ng' => ['name' => 'First Bank', 'type' => 'bank'],
        'nibss-ng' => ['name' => 'NIBSS', 'type' => 'api'],
        'remita-ng' => ['name' => 'Remita', 'type' => 'api'],
        'kuda' => ['name' => 'Kuda', 'type' => 'wallet'],
        'chippercash-ng' => ['name' => 'Chipper Cash', 'type' => 'wallet'],
    ],

    'MK' => [
        'komercijalna' => ['name' => 'Komercijalna Banka', 'type' => 'bank'],
        'stopanska' => ['name' => 'Stopanska Banka', 'type' => 'bank'],
        'nlb-mk' => ['name' => 'NLB Banka', 'type' => 'bank'],
        'mk-pay' => ['name' => 'MacedoniaPay', 'type' => 'api'],
        'halo-mk' => ['name' => 'Halo', 'type' => 'mobile'],
    ],

    'NO' => [
        'vipps-no' => ['name' => 'Vipps', 'type' => 'wallet'],
        'nets-no' => ['name' => 'Nets Norway', 'type' => 'api'],
        'dnb' => ['name' => 'DNB', 'type' => 'bank'],
        'nordea-no' => ['name' => 'Nordea Norway', 'type' => 'bank'],
        'sparebank1' => ['name' => 'SpareBank 1', 'type' => 'bank'],
        'klarna-no' => ['name' => 'Klarna Norway', 'type' => 'api'],
        'viabill-no' => ['name' => 'ViaBill Norway', 'type' => 'api'],
    ],

    'OM' => [
        'omantel-om' => ['name' => 'Omantel', 'type' => 'api'],
        'mobicash-om' => ['name' => 'Mobicash', 'type' => 'mobile'],
        'bank-muscat' => ['name' => 'Bank Muscat', 'type' => 'bank'],
        'hsbc-om' => ['name' => 'HSBC Oman', 'type' => 'bank'],
        'dhofar' => ['name' => 'Bank Dhofar', 'type' => 'bank'],
        'om-pay' => ['name' => 'Oman Pay', 'type' => 'api'],
    ],

    'PK' => [
        'easypaisa-pk' => ['name' => 'Easypaisa', 'type' => 'mobile', 'driver' => 'Hadi\\Payment\\Gateways\\EasypaisaGateway'],
        'jazzcash-pk' => ['name' => 'JazzCash', 'type' => 'mobile', 'driver' => 'Hadi\\Payment\\Gateways\\JazzcashGateway'],
        'sadapay-pk' => ['name' => 'SadaPay', 'type' => 'api'],
        'nayapay-pk' => ['name' => 'NayaPay', 'type' => 'api'],
        '1link-pk' => ['name' => '1Link', 'type' => 'api'],
        'payfast-pk' => ['name' => 'PayFast Pakistan', 'type' => 'api'],
        'hbl-pk' => ['name' => 'HBL', 'type' => 'bank'],
        'ubl-pk' => ['name' => 'UBL', 'type' => 'bank'],
        'mcb-pk' => ['name' => 'MCB', 'type' => 'bank'],
        'allied-pk' => ['name' => 'Allied Bank', 'type' => 'bank'],
        'meezan-pk' => ['name' => 'Meezan Bank', 'type' => 'bank'],
        'alkaram' => ['name' => 'Al-Karam', 'type' => 'api'],
        'techvalley' => ['name' => 'Techvalley', 'type' => 'api'],
        'upsolv' => ['name' => 'Upsolv', 'type' => 'api'],
    ],

    'PW' => [
        'boc-pw' => ['name' => 'Bank of Guam Palau', 'type' => 'bank'],
        'pncc' => ['name' => 'Palau National Bank', 'type' => 'bank'],
        'palau-tel' => ['name' => 'Palau Telecom', 'type' => 'mobile'],
    ],

    'PS' => [
        'palpay-ps' => ['name' => 'PalPay', 'type' => 'api'],
        'bank-of-palestine' => ['name' => 'Bank of Palestine', 'type' => 'bank'],
        'arab-bank-ps' => ['name' => 'Arab Bank Palestine', 'type' => 'bank'],
        'pal-telecom' => ['name' => 'Paltel', 'type' => 'mobile'],
        'jawwal' => ['name' => 'Jawwal Pay', 'type' => 'mobile'],
    ],

    'PA' => [
        'bac-pa' => ['name' => 'BAC Credomatic Panama', 'type' => 'api'],
        'banco-general' => ['name' => 'Banco General', 'type' => 'bank'],
        'banistmo' => ['name' => 'Banistmo', 'type' => 'bank'],
        'yappy' => ['name' => 'Yappy', 'type' => 'mobile'],
        'nequi-pa' => ['name' => 'Nequi Panama', 'type' => 'mobile'],
        'pago-pa-pan' => ['name' => 'Pago Panama', 'type' => 'api'],
    ],

    'PG' => [
        'b-spg' => ['name' => 'BSP Papua New Guinea', 'type' => 'bank'],
        'kina-bank' => ['name' => 'Kina Bank', 'type' => 'bank'],
        'anz-pg' => ['name' => 'ANZ PNG', 'type' => 'bank'],
        'digicel-pg' => ['name' => 'Digicel PNG', 'type' => 'mobile'],
        'b-mobile-pg' => ['name' => 'bMobile', 'type' => 'mobile'],
        'yes-pay-pg' => ['name' => 'Yes Pay', 'type' => 'mobile'],
    ],

    'PY' => [
        'pago-py' => ['name' => 'Pago Paraguay', 'type' => 'api'],
        'banco-itau-py' => ['name' => 'Itaú Paraguay', 'type' => 'bank'],
        'banco-bas' => ['name' => 'Banco BASA', 'type' => 'bank'],
        'vision-bank' => ['name' => 'Banco Visión', 'type' => 'bank'],
        'tigo-py' => ['name' => 'Tigo Money Paraguay', 'type' => 'mobile'],
        'billetera-py' => ['name' => 'Billetera', 'type' => 'wallet'],
    ],

    'PE' => [
        'niubiz-pe' => ['name' => 'Niubiz', 'type' => 'api'],
        'pagoefectivo-pe' => ['name' => 'PagoEfectivo', 'type' => 'api'],
        'yape-pe' => ['name' => 'Yape', 'type' => 'wallet'],
        'plin-pe' => ['name' => 'Plin', 'type' => 'wallet'],
        'bcp' => ['name' => 'BCP', 'type' => 'bank'],
        'bbva-pe' => ['name' => 'BBVA Perú', 'type' => 'bank'],
        'interbank' => ['name' => 'Interbank', 'type' => 'bank'],
        'visanet-pe' => ['name' => 'VisaNet', 'type' => 'api'],
        'bizum-pe' => ['name' => 'Bizum Perú', 'type' => 'mobile'],
    ],

    'PH' => [
        'gcash-ph' => ['name' => 'GCash', 'type' => 'wallet'],
        'paymaya-ph' => ['name' => 'Maya', 'type' => 'wallet'],
        'dragonpay-ph' => ['name' => 'Dragonpay', 'type' => 'api'],
        'paymongo-ph' => ['name' => 'PayMongo', 'type' => 'api'],
        'bdo-ph' => ['name' => 'BDO', 'type' => 'bank'],
        'bpi' => ['name' => 'BPI', 'type' => 'bank'],
        'metrobank' => ['name' => 'Metrobank', 'type' => 'bank'],
        'paymaya-enterprise' => ['name' => 'Maya Enterprise', 'type' => 'api'],
        'coins-ph' => ['name' => 'Coins.ph', 'type' => 'wallet'],
        'grabpay-ph' => ['name' => 'GrabPay Philippines', 'type' => 'wallet'],
        'peso-pay' => ['name' => 'PesoPay', 'type' => 'api'],
        'unionbank-ph' => ['name' => 'UnionBank', 'type' => 'bank'],
    ],

    'PL' => [
        'przelewy24-pl' => ['name' => 'Przelewy24', 'type' => 'bank'],
        'blik-pl' => ['name' => 'BLIK', 'type' => 'bank'],
        'payu-pl' => ['name' => 'PayU Poland', 'type' => 'api'],
        'tpay' => ['name' => 'Tpay', 'type' => 'api'],
        'dotpay' => ['name' => 'Dotpay', 'type' => 'api'],
        'pkobp' => ['name' => 'PKO BP', 'type' => 'bank'],
        'mbank' => ['name' => 'mBank', 'type' => 'bank'],
        'ing-pl' => ['name' => 'ING Poland', 'type' => 'bank'],
        'pekao' => ['name' => 'Pekao', 'type' => 'bank'],
        'skrill-pl' => ['name' => 'Skrill Poland', 'type' => 'api'],
        'paynow-pl' => ['name' => 'PayNow Poland', 'type' => 'api'],
    ],

    'PT' => [
        'multibanco-pt' => ['name' => 'Multibanco', 'type' => 'bank'],
        'mbway' => ['name' => 'MB WAY', 'type' => 'wallet'],
        'sibs-pt' => ['name' => 'SIBS', 'type' => 'api'],
        'millennium-bcp' => ['name' => 'Millennium BCP', 'type' => 'bank'],
        'cgd' => ['name' => 'Caixa Geral de Depósitos', 'type' => 'bank'],
        'novo-banco-pt' => ['name' => 'Novo Banco', 'type' => 'bank'],
        'eupago' => ['name' => 'Eupago', 'type' => 'api'],
        'ifthenpay' => ['name' => 'Ifthenpay', 'type' => 'api'],
    ],

    'QA' => [
        'qpay-qa' => ['name' => 'QPay', 'type' => 'api'],
        'qnb-qa' => ['name' => 'QNB', 'type' => 'bank'],
        'doha-bank-qa' => ['name' => 'Doha Bank', 'type' => 'bank'],
        'commercial-bank-qa' => ['name' => 'Commercial Bank of Qatar', 'type' => 'bank'],
        'ooredoo-qa' => ['name' => 'Ooredoo Qatar', 'type' => 'mobile'],
        'vodafone-qa' => ['name' => 'Vodafone Qatar', 'type' => 'mobile'],
        'qatar-pay' => ['name' => 'QatarPay', 'type' => 'api'],
    ],

    'RO' => [
        'euplatesc-ro' => ['name' => 'Euplatesc', 'type' => 'api'],
        'netopia-ro' => ['name' => 'Netopia', 'type' => 'api'],
        'payu-ro' => ['name' => 'PayU Romania', 'type' => 'api'],
        'bcr' => ['name' => 'BCR', 'type' => 'bank'],
        'brd' => ['name' => 'BRD', 'type' => 'bank'],
        'banca-transilvania' => ['name' => 'Banca Transilvania', 'type' => 'bank'],
        'cardmoto' => ['name' => 'CardMoto', 'type' => 'api'],
        'paypoint-ro' => ['name' => 'PayPoint', 'type' => 'api'],
    ],

    'RU' => [
        'sberbank-ru' => ['name' => 'Sberbank', 'type' => 'api'],
        'tinkoff-ru' => ['name' => 'Tinkoff', 'type' => 'api'],
        'yookassa-ru' => ['name' => 'YooKassa', 'type' => 'api'],
        'qiwi' => ['name' => 'QIWI', 'type' => 'wallet'],
        'webmoney' => ['name' => 'WebMoney', 'type' => 'wallet'],
        'yandex-money' => ['name' => 'Yandex Money', 'type' => 'wallet'],
        'vtb' => ['name' => 'VTB', 'type' => 'bank'],
        'alfabank' => ['name' => 'Alfa-Bank', 'type' => 'bank'],
        'mir' => ['name' => 'MIR', 'type' => 'card'],
        'robokassa-ru' => ['name' => 'Robokassa', 'type' => 'api'],
        'unitpay' => ['name' => 'UnitPay', 'type' => 'api'],
        'cloudpayments' => ['name' => 'CloudPayments', 'type' => 'api'],
        'paykeeper' => ['name' => 'PayKeeper', 'type' => 'api'],
        'inplat' => ['name' => 'Inplat', 'type' => 'api'],
    ],

    'RW' => [
        'm-pesa-rw-local' => ['name' => 'M-Pesa Rwanda', 'type' => 'mobile'],
        'mtn-momo-rw' => ['name' => 'MTN MoMo Rwanda', 'type' => 'mobile'],
        'airtel-rw' => ['name' => 'Airtel Money Rwanda', 'type' => 'mobile'],
        'bk-rw' => ['name' => 'Bank of Kigali', 'type' => 'bank'],
        'equity-rw' => ['name' => 'Equity Bank Rwanda', 'type' => 'bank'],
        'ipay-rw' => ['name' => 'iPay', 'type' => 'api'],
    ],

    'KN' => [
        'sknc' => ['name' => 'St. Kitts National Bank', 'type' => 'bank'],
        'skn-pay' => ['name' => 'St. Kitts Pay', 'type' => 'api'],
        'firstcaribbean-kn' => ['name' => 'CIBC FirstCaribbean St. Kitts', 'type' => 'bank'],
    ],

    'LC' => [
        'lccb' => ['name' => '1st National Bank St. Lucia', 'type' => 'bank'],
        'bank-of-saint-lucia' => ['name' => 'Bank of Saint Lucia', 'type' => 'bank'],
        'sl-pay' => ['name' => 'St. Lucia Pay', 'type' => 'api'],
    ],

    'VC' => [
        'bnb-vc' => ['name' => 'Bank of St. Vincent', 'type' => 'bank'],
        'cbb-vc' => ['name' => 'CIBC FirstCaribbean St. Vincent', 'type' => 'bank'],
        'sv-pay' => ['name' => 'St. Vincent Pay', 'type' => 'api'],
    ],

    'WS' => [
        'bos-samoa' => ['name' => 'Bank of Samoa', 'type' => 'bank'],
        'samoa-national' => ['name' => 'Samoa National Bank', 'type' => 'bank'],
        'samoa-tel' => ['name' => 'SamoaTel', 'type' => 'mobile'],
        'm-paisa-ws' => ['name' => 'M-Paisa Samoa', 'type' => 'mobile'],
    ],

    'SM' => [
        'bcs-sm' => ['name' => 'Banca Centrale Sammarinese', 'type' => 'bank'],
        'carisp' => ['name' => 'Cassa di Risparmio', 'type' => 'bank'],
        'sm-pay' => ['name' => 'San Marino Pay', 'type' => 'api'],
    ],

    'ST' => [
        'bctp' => ['name' => 'BCTP', 'type' => 'bank'],
        'movicel' => ['name' => 'Movicel', 'type' => 'mobile'],
        'unitel-st' => ['name' => 'Unitel São Tomé', 'type' => 'mobile'],
        'st-pay' => ['name' => 'São Tomé Pay', 'type' => 'api'],
    ],

    'SA' => [
        'stcpay-sa' => ['name' => 'STC Pay', 'type' => 'mobile', 'driver' => 'Hadi\\Payment\\Gateways\\StcpayGateway'],
        'mada-sa' => ['name' => 'Mada', 'type' => 'card'],
        'sadad-sa' => ['name' => 'SADAD', 'type' => 'api'],
        'alrajhi-sa' => ['name' => 'Al Rajhi Bank', 'type' => 'bank'],
        'snb' => ['name' => 'SNB', 'type' => 'bank'],
        'samba' => ['name' => 'Samba', 'type' => 'bank'],
        'riyad' => ['name' => 'Riyad Bank', 'type' => 'bank'],
        'hyperpay-sa' => ['name' => 'HyperPay', 'type' => 'api'],
        'geidea-sa' => ['name' => 'Geidea', 'type' => 'api'],
        'tabby-sa' => ['name' => 'Tabby', 'type' => 'api'],
        'tamara-sa' => ['name' => 'Tamara', 'type' => 'api'],
        'paytabs-sa' => ['name' => 'PayTabs Saudi', 'type' => 'api'],
        'applepay-sa' => ['name' => 'Apple Pay Saudi', 'type' => 'wallet'],
    ],

    'SN' => [
        'orange-sn-cash' => ['name' => 'Orange Money Senegal', 'type' => 'mobile'],
        'wave-sn' => ['name' => 'Wave Senegal', 'type' => 'mobile'],
        'free-money' => ['name' => 'Free Money', 'type' => 'mobile'],
        'bhs' => ['name' => 'BHS', 'type' => 'bank'],
        'sgb' => ['name' => 'SGBS', 'type' => 'bank'],
        'sn-pay' => ['name' => 'SenegalPay', 'type' => 'api'],
    ],

    'RS' => [
        'paysera-rs' => ['name' => 'Paysera Serbia', 'type' => 'api'],
        'rais-rs' => ['name' => 'Raiffeisen Serbia', 'type' => 'bank'],
        'komercijalna-rs' => ['name' => 'Komercijalna Banka', 'type' => 'bank'],
        'intesa-rs' => ['name' => 'Banca Intesa Beograd', 'type' => 'bank'],
        'sve-pay' => ['name' => 'SvePay', 'type' => 'api'],
        'telemach' => ['name' => 'Telemach', 'type' => 'mobile'],
    ],

    'SC' => [
        'mcb-sey' => ['name' => 'MCB Seychelles', 'type' => 'bank'],
        'nouvobanq' => ['name' => 'Nouvobanq', 'type' => 'bank'],
        'sey-tel' => ['name' => 'Seychelles Telecom', 'type' => 'mobile'],
        'sey-pay' => ['name' => 'SeyPay', 'type' => 'api'],
    ],

    'SL' => [
        'orange-sl' => ['name' => 'Orange Money Sierra Leone', 'type' => 'mobile'],
        'afrimoney-sl' => ['name' => 'Afrimoney Sierra Leone', 'type' => 'mobile'],
        'rokel' => ['name' => 'Rokel Bank', 'type' => 'bank'],
        'sl-pay-gov' => ['name' => 'Sierra Leone Pay', 'type' => 'api'],
    ],

    'SG' => [
        'paynow-sg' => ['name' => 'PayNow', 'type' => 'bank'],
        'grabpay-sg' => ['name' => 'GrabPay Singapore', 'type' => 'wallet'],
        'shopee-sg' => ['name' => 'ShopeePay Singapore', 'type' => 'wallet'],
        'paylah' => ['name' => 'PayLah!', 'type' => 'wallet'],
        'singtel-dash' => ['name' => 'Singtel Dash', 'type' => 'wallet'],
        'dbs' => ['name' => 'DBS', 'type' => 'bank'],
        'ocbc' => ['name' => 'OCBC', 'type' => 'bank'],
        'uob' => ['name' => 'UOB', 'type' => 'bank'],
        'hitpay' => ['name' => 'HitPay', 'type' => 'api'],
        'stripe-sg' => ['name' => 'Stripe Singapore', 'type' => 'api'],
        'airwallex-sg' => ['name' => 'Airwallex Singapore', 'type' => 'api'],
        'grab-sg' => ['name' => 'Grab', 'type' => 'wallet'],
    ],

    'SK' => [
        'gopay-sk' => ['name' => 'GoPay Slovakia', 'type' => 'api'],
        'slsp' => ['name' => 'Slovenská sporiteľňa', 'type' => 'bank'],
        'vub' => ['name' => 'VÚB', 'type' => 'bank'],
        'tatrabanka' => ['name' => 'Tatra banka', 'type' => 'bank'],
        'sk-pay' => ['name' => 'SlovakPay', 'type' => 'api'],
        'globalpayments-sk' => ['name' => 'Global Payments Slovakia', 'type' => 'api'],
    ],

    'SI' => [
        'monetra' => ['name' => 'Monetra', 'type' => 'api'],
        'nlb-si' => ['name' => 'NLB', 'type' => 'bank'],
        'nkbm' => ['name' => 'NKBM', 'type' => 'bank'],
        'abanka' => ['name' => 'Abanka', 'type' => 'bank'],
        'slovenia-pay' => ['name' => 'SloveniaPay', 'type' => 'api'],
    ],

    'SB' => [
        'bsp-sb' => ['name' => 'BSP Solomon Islands', 'type' => 'bank'],
        'sibc' => ['name' => 'SIBC', 'type' => 'bank'],
        'our-telekom' => ['name' => 'Our Telekom', 'type' => 'mobile'],
        'b-mobile-sb' => ['name' => 'bMobile', 'type' => 'mobile'],
    ],

    'SO' => [
        'e-dahab-so' => ['name' => 'E-Dahab', 'type' => 'mobile'],
        'zaad' => ['name' => 'Zaad', 'type' => 'mobile'],
        'sahal' => ['name' => 'Sahal', 'type' => 'mobile'],
        'cbs' => ['name' => 'Central Bank of Somalia', 'type' => 'bank'],
        'dahabshiil-so' => ['name' => 'Dahabshiil', 'type' => 'api'],
        'telesom' => ['name' => 'Telesom', 'type' => 'mobile'],
    ],

    'ZA' => [
        'payfast-za' => ['name' => 'PayFast', 'type' => 'api', 'driver' => 'Hadi\\Payment\\Gateways\\PayFastGateway'],
        'ozow-za' => ['name' => 'Ozow', 'type' => 'bank'],
        'paygate' => ['name' => 'PayGate', 'type' => 'api'],
        'peachpayments-za' => ['name' => 'Peach Payments', 'type' => 'api'],
        'yoco-za' => ['name' => 'Yoco', 'type' => 'api'],
        'snapscan' => ['name' => 'SnapScan', 'type' => 'mobile'],
        'zapper' => ['name' => 'Zapper', 'type' => 'mobile'],
        'absa-za' => ['name' => 'Absa', 'type' => 'bank'],
        'standard-bank-za' => ['name' => 'Standard Bank', 'type' => 'bank'],
        'fnb-za' => ['name' => 'FNB', 'type' => 'bank'],
        'capitec' => ['name' => 'Capitec', 'type' => 'bank'],
        'safaricom-za' => ['name' => 'Safaricom SA', 'type' => 'mobile'],
        'stripe-za' => ['name' => 'Stripe South Africa', 'type' => 'api'],
        'paypal-za' => ['name' => 'PayPal South Africa', 'type' => 'api'],
    ],

    'SS' => [
        'm-pesa-ss' => ['name' => 'M-Pesa South Sudan', 'type' => 'mobile'],
        'mtn-ss' => ['name' => 'MTN MoMo South Sudan', 'type' => 'mobile'],
        'bsd' => ['name' => 'Bank of South Sudan', 'type' => 'bank'],
        'ecobank-ss' => ['name' => 'Ecobank South Sudan', 'type' => 'bank'],
    ],

    'ES' => [
        'redsys-es' => ['name' => 'Redsys', 'type' => 'api'],
        'bizum-es' => ['name' => 'Bizum', 'type' => 'mobile'],
        'paycomet-es' => ['name' => 'Paycomet', 'type' => 'api'],
        'santander-es' => ['name' => 'Santander', 'type' => 'bank'],
        'bbva-es' => ['name' => 'BBVA', 'type' => 'bank'],
        'caixabank' => ['name' => 'CaixaBank', 'type' => 'bank'],
        'sabadell' => ['name' => 'Banco Sabadell', 'type' => 'bank'],
        'cetelem' => ['name' => 'Cetelem', 'type' => 'api'],
        'sequra' => ['name' => 'SeQura', 'type' => 'api'],
        'paysera-es' => ['name' => 'Paysera Spain', 'type' => 'api'],
    ],

    'LK' => [
        'sampath' => ['name' => 'Sampath Bank', 'type' => 'api'],
        'combank-lk' => ['name' => 'Commercial Bank', 'type' => 'bank'],
        'hutch-lk' => ['name' => 'Hutch', 'type' => 'mobile'],
        'dialog-lk' => ['name' => 'Dialog', 'type' => 'mobile'],
        'ezcash-lk' => ['name' => 'EZ Cash', 'type' => 'mobile'],
        'mobitel-lk' => ['name' => 'Mobitel', 'type' => 'mobile'],
        'boc-lk' => ['name' => 'Bank of Ceylon', 'type' => 'bank'],
        'payhere-lk' => ['name' => 'PayHere', 'type' => 'api'],
        'slt-mobitel' => ['name' => 'SLT Mobitel', 'type' => 'mobile'],
    ],

    'SD' => [
        'fawry-sd' => ['name' => 'Fawry Sudan', 'type' => 'api'],
        'bank-khartoum' => ['name' => 'Bank of Khartoum', 'type' => 'bank'],
        'faisal-bank-sd' => ['name' => 'Faisal Bank', 'type' => 'bank'],
        'sd-pay' => ['name' => 'SudanPay', 'type' => 'api'],
        'zain-sd' => ['name' => 'Zain Sudan', 'type' => 'mobile'],
    ],

    'SR' => [
        'hakrinbank' => ['name' => 'Hakrinbank', 'type' => 'bank'],
        'dbs-suriname' => ['name' => 'DSB Bank', 'type' => 'bank'],
        'vcb' => ['name' => 'Verenigde CBS Bank', 'type' => 'bank'],
        'telesur' => ['name' => 'Telesur', 'type' => 'mobile'],
        'digicel-sr' => ['name' => 'Digicel Suriname', 'type' => 'mobile'],
        'sur-pay' => ['name' => 'SurPay', 'type' => 'api'],
    ],

    'SE' => [
        'klarna-se' => ['name' => 'Klarna', 'type' => 'api'],
        'swish-se' => ['name' => 'Swish', 'type' => 'wallet'],
        'trustly-se' => ['name' => 'Trustly', 'type' => 'bank'],
        'nordea-se' => ['name' => 'Nordea', 'type' => 'bank'],
        'seb-se' => ['name' => 'SEB', 'type' => 'bank'],
        'handelsbanken' => ['name' => 'Handelsbanken', 'type' => 'bank'],
        'swedbank-se' => ['name' => 'Swedbank', 'type' => 'bank'],
        'paypal-se' => ['name' => 'PayPal Sweden', 'type' => 'api'],
        'zimpler' => ['name' => 'Zimpler', 'type' => 'api'],
        'qliro' => ['name' => 'Qliro', 'type' => 'api'],
    ],

    'CH' => [
        'twint-ch' => ['name' => 'TWINT', 'type' => 'wallet'],
        'datatrans-ch' => ['name' => 'Datatrans', 'type' => 'api'],
        'saferpay-ch' => ['name' => 'Saferpay', 'type' => 'api'],
        'payrexx' => ['name' => 'Payrexx', 'type' => 'api'],
        'ubs' => ['name' => 'UBS', 'type' => 'bank'],
        'credit-suisse' => ['name' => 'Credit Suisse', 'type' => 'bank'],
        'zkb' => ['name' => 'ZKB', 'type' => 'bank'],
        'raiffeisen-ch' => ['name' => 'Raiffeisen Switzerland', 'type' => 'bank'],
        'six-payment' => ['name' => 'SIX Payment Services', 'type' => 'api'],
        'cornercard' => ['name' => 'Cornercard', 'type' => 'api'],
    ],

    'SY' => [
        'syriatel-sy' => ['name' => 'Syriatel Cash', 'type' => 'mobile'],
        'mtn-sy' => ['name' => 'MTN Syria', 'type' => 'mobile'],
        'cbs-sy' => ['name' => 'Central Bank of Syria', 'type' => 'bank'],
        'sy-pay' => ['name' => 'SyriaPay', 'type' => 'api'],
    ],

    'TJ' => [
        'dushanbe-city-tj' => ['name' => 'Dushanbe City', 'type' => 'api'],
        'halyk-tj' => ['name' => 'Halyk Tajikistan', 'type' => 'bank'],
        'amo-tj' => ['name' => 'Amonatbank', 'type' => 'bank'],
        'moliya' => ['name' => 'Moliya', 'type' => 'api'],
        'tcell' => ['name' => 'Tcell', 'type' => 'mobile'],
        'bee-tj' => ['name' => 'Bee', 'type' => 'mobile'],
    ],

    'TZ' => [
        'm-pesa-tz-local' => ['name' => 'M-Pesa Tanzania', 'type' => 'mobile'],
        'tigo-tz-local' => ['name' => 'Tigo Pesa', 'type' => 'mobile'],
        'airtel-tz-local' => ['name' => 'Airtel Money Tanzania', 'type' => 'mobile'],
        'halopesa-tz' => ['name' => 'HaloPesa', 'type' => 'mobile'],
        'crdb' => ['name' => 'CRDB', 'type' => 'bank'],
        'nmb-tz' => ['name' => 'NMB', 'type' => 'bank'],
        'nb-tz' => ['name' => 'National Bank of Commerce', 'type' => 'bank'],
        'tigo-pesa-tz' => ['name' => 'Tigo Pesa', 'type' => 'mobile'],
        'zantel-ezy' => ['name' => 'Zantel EzyPesa', 'type' => 'mobile'],
    ],

    'TH' => [
        'promptpay-th' => ['name' => 'PromptPay', 'type' => 'bank'],
        'truewallet-th' => ['name' => 'TrueMoney', 'type' => 'wallet'],
        'omise-th' => ['name' => 'Omise', 'type' => 'api'],
        'scb-th' => ['name' => 'SCB', 'type' => 'bank'],
        'kbank-th' => ['name' => 'Kasikorn', 'type' => 'bank'],
        'bangkok-bank' => ['name' => 'Bangkok Bank', 'type' => 'bank'],
        'rabbit-line-pay' => ['name' => 'Rabbit LINE Pay', 'type' => 'wallet'],
        'airpay-th' => ['name' => 'AirPay', 'type' => 'wallet'],
        'm-pesa-th' => ['name' => 'M-Pesa Thailand', 'type' => 'mobile'],
        '2c2p-th' => ['name' => '2C2P Thailand', 'type' => 'api'],
    ],

    'TL' => [
        'bca-tl' => ['name' => 'BNCTL', 'type' => 'bank'],
        'telemor-tl' => ['name' => 'Telemor', 'type' => 'mobile'],
        'telkomcel' => ['name' => 'Telkomcel', 'type' => 'mobile'],
        'timor-tel' => ['name' => 'Timor Telecom', 'type' => 'mobile'],
        'tl-pay' => ['name' => 'TimorPay', 'type' => 'api'],
    ],

    'TG' => [
        'flooz-tg' => ['name' => 'Flooz', 'type' => 'mobile'],
        'moov-tg' => ['name' => 'Moov Money Togo', 'type' => 'mobile'],
        'togo-cash' => ['name' => 'Togo Cash', 'type' => 'mobile'],
        'utb' => ['name' => 'UTB', 'type' => 'bank'],
        'btg' => ['name' => 'BTG', 'type' => 'bank'],
        'ecobank-tg' => ['name' => 'Ecobank Togo', 'type' => 'bank'],
    ],

    'TO' => [
        'm-bank-to' => ['name' => 'MBank Tonga', 'type' => 'bank'],
        'bst' => ['name' => 'Bank South Pacific Tonga', 'type' => 'bank'],
        'digicel-to' => ['name' => 'Digicel Tonga', 'type' => 'mobile'],
        'tonga-pay' => ['name' => 'TongaPay', 'type' => 'api'],
    ],

    'TT' => [
        'republic-bank-tt' => ['name' => 'Republic Bank', 'type' => 'api'],
        'firstcaribbean-tt' => ['name' => 'CIBC FirstCaribbean Trinidad', 'type' => 'bank'],
        'scotiabank-tt' => ['name' => 'Scotiabank Trinidad', 'type' => 'bank'],
        'rbtt' => ['name' => 'RBTT', 'type' => 'bank'],
        'tstt' => ['name' => 'TSTT', 'type' => 'mobile'],
        'digicel-tt' => ['name' => 'Digicel Trinidad', 'type' => 'mobile'],
        'tt-pay' => ['name' => 'TrinPay', 'type' => 'api'],
    ],

    'TN' => [
        'flouci' => ['name' => 'Flouci', 'type' => 'wallet'],
        'd17' => ['name' => 'D17', 'type' => 'api'],
        'smartpay-tn' => ['name' => 'SmartPay', 'type' => 'api'],
        'bhb' => ['name' => 'BH Bank', 'type' => 'bank'],
        'amen-bank' => ['name' => 'Amen Bank', 'type' => 'bank'],
        'atb' => ['name' => 'ATB', 'type' => 'bank'],
        'ooredoo-tn' => ['name' => 'Ooredoo Tunisia', 'type' => 'mobile'],
        'oore-tn' => ['name' => 'Ooredoo Pay', 'type' => 'mobile'],
        'tn-pay' => ['name' => 'TunisiaPay', 'type' => 'api'],
    ],

    'TR' => [
        'iyzico-tr' => ['name' => 'iyzico', 'type' => 'api'],
        'paytr-tr' => ['name' => 'PayTR', 'type' => 'api'],
        'papara-tr' => ['name' => 'Papara', 'type' => 'wallet'],
        'ozan-tr' => ['name' => 'Ozan', 'type' => 'api'],
        'param-tr' => ['name' => 'Param', 'type' => 'api'],
        'isbank' => ['name' => 'İş Bankası', 'type' => 'bank'],
        'garanti' => ['name' => 'Garanti BBVA', 'type' => 'bank'],
        'akbank' => ['name' => 'Akbank', 'type' => 'bank'],
        'yapi-kredi' => ['name' => 'Yapı Kredi', 'type' => 'bank'],
        'ziraat' => ['name' => 'Ziraat Bankası', 'type' => 'bank'],
        'troy' => ['name' => 'Troy', 'type' => 'card'],
        'param-pos' => ['name' => 'Param POS', 'type' => 'api'],
        'sipay' => ['name' => 'Sipay', 'type' => 'api'],
        'iyzilink' => ['name' => 'iyzilink', 'type' => 'api'],
        'turkpos' => ['name' => 'TurkPOS', 'type' => 'api'],
    ],

    'TM' => [
        'rbt' => ['name' => 'RBT', 'type' => 'mobile'],
        'tm-cell' => ['name' => 'TM-Cell', 'type' => 'mobile'],
        'daykhan' => ['name' => 'Daykhan Bank', 'type' => 'bank'],
        'senagat' => ['name' => 'Senagat Bank', 'type' => 'bank'],
        'tm-pay' => ['name' => 'TurkmenPay', 'type' => 'api'],
    ],

    'TV' => [
        'ntb' => ['name' => 'National Bank of Tuvalu', 'type' => 'bank'],
        'tuvalu-tel' => ['name' => 'Tuvalu Telecom', 'type' => 'mobile'],
        'tuvalu-pay' => ['name' => 'TuvaluPay', 'type' => 'api'],
    ],

    'UG' => [
        'mtn-ug-momo' => ['name' => 'MTN MoMo Uganda', 'type' => 'mobile'],
        'airtel-ug-local' => ['name' => 'Airtel Money Uganda', 'type' => 'mobile'],
        'm-pesa-ug-local' => ['name' => 'M-Pesa Uganda', 'type' => 'mobile'],
        'stanbic-ug' => ['name' => 'Stanbic Uganda', 'type' => 'bank'],
        'dfcu' => ['name' => 'dfcu Bank', 'type' => 'bank'],
        'equity-ug' => ['name' => 'Equity Bank Uganda', 'type' => 'bank'],
        'pesapal-ug' => ['name' => 'PesaPal', 'type' => 'api'],
        'momo-pay' => ['name' => 'MoMo Pay', 'type' => 'mobile'],
    ],

    'UA' => [
        'privat24-ua' => ['name' => 'Privat24', 'type' => 'wallet'],
        'liqpay-ua' => ['name' => 'LiqPay', 'type' => 'api'],
        'fondy-ua' => ['name' => 'Fondy', 'type' => 'api'],
        'monobank-ua' => ['name' => 'MonoBank', 'type' => 'api'],
        'privatbank' => ['name' => 'PrivatBank', 'type' => 'bank'],
        'oshadbank' => ['name' => 'Oshadbank', 'type' => 'bank'],
        'raiffeisen-ua' => ['name' => 'Raiffeisen Ukraine', 'type' => 'bank'],
        'wayforpay' => ['name' => 'WayForPay', 'type' => 'api'],
        'portmone' => ['name' => 'Portmone', 'type' => 'api'],
        'interkassa' => ['name' => 'Interkassa', 'type' => 'api'],
        'easypay-ua' => ['name' => 'EasyPay', 'type' => 'wallet'],
    ],

    'AE' => [
        'checkout-ae' => ['name' => 'Checkout.com UAE', 'type' => 'api'],
        'payfort-ae' => ['name' => 'PayFort', 'type' => 'api'],
        'telr-ae' => ['name' => 'Telr', 'type' => 'api'],
        'mint-ae' => ['name' => 'Mint', 'type' => 'api'],
        'enbd' => ['name' => 'Emirates NBD', 'type' => 'bank'],
        'adcb' => ['name' => 'ADCB', 'type' => 'bank'],
        'dubai-islamic' => ['name' => 'Dubai Islamic Bank', 'type' => 'bank'],
        'mamo' => ['name' => 'Mamo Pay', 'type' => 'wallet'],
        'beam' => ['name' => 'Beam Wallet', 'type' => 'wallet'],
        'tabby-ae' => ['name' => 'Tabby UAE', 'type' => 'api'],
        'klivvr-ae' => ['name' => 'Klivvr', 'type' => 'wallet'],
        'bunq' => ['name' => 'Bunq', 'type' => 'wallet'],
    ],

    'GB' => [
        'stripe-gb' => ['name' => 'Stripe UK', 'type' => 'api'],
        'worldpay-gb' => ['name' => 'Worldpay UK', 'type' => 'api'],
        'barclays-epdq' => ['name' => 'Barclaycard ePDQ', 'type' => 'api'],
        'hsbc-gb' => ['name' => 'HSBC UK', 'type' => 'bank'],
        'barclays-gb' => ['name' => 'Barclays', 'type' => 'bank'],
        'lloyds' => ['name' => 'Lloyds', 'type' => 'bank'],
        'natwest' => ['name' => 'NatWest', 'type' => 'bank'],
        'faster-payments' => ['name' => 'Faster Payments', 'type' => 'bank'],
        'paypal-gb' => ['name' => 'PayPal UK', 'type' => 'api'],
        'klarna-gb' => ['name' => 'Klarna UK', 'type' => 'api'],
        'clearpay' => ['name' => 'Clearpay', 'type' => 'api'],
        'monzo' => ['name' => 'Monzo', 'type' => 'wallet'],
        'starling' => ['name' => 'Starling Bank', 'type' => 'bank'],
        'truelayer' => ['name' => 'TrueLayer', 'type' => 'api'],
        'directdebit' => ['name' => 'Direct Debit', 'type' => 'bank'],
    ],

    'US' => [
        'stripe-us' => ['name' => 'Stripe', 'type' => 'api', 'driver' => 'Hadi\\Payment\\Gateways\\StripeGateway'],
        'square-us' => ['name' => 'Square', 'type' => 'api'],
        'authorize-us' => ['name' => 'Authorize.Net', 'type' => 'api'],
        'braintree-us' => ['name' => 'Braintree', 'type' => 'api'],
        'paypal-us' => ['name' => 'PayPal', 'type' => 'api', 'driver' => 'Hadi\\Payment\\Gateways\\PayPalGateway'],
        'ach-us' => ['name' => 'ACH', 'type' => 'bank'],
        'venmo-us' => ['name' => 'Venmo', 'type' => 'wallet'],
        'zelle-us' => ['name' => 'Zelle', 'type' => 'bank'],
        'cashapp-us' => ['name' => 'Cash App', 'type' => 'wallet'],
        'chase-us' => ['name' => 'Chase Paymentech', 'type' => 'api'],
        'bofa' => ['name' => 'Bank of America', 'type' => 'bank'],
        'wellsfargo-us' => ['name' => 'Wells Fargo', 'type' => 'bank'],
        'citibank' => ['name' => 'Citi', 'type' => 'bank'],
        'adp' => ['name' => 'ADP', 'type' => 'api'],
        'afterpay-us' => ['name' => 'Afterpay US', 'type' => 'api'],
        'klarna-us' => ['name' => 'Klarna US', 'type' => 'api'],
        'affirm' => ['name' => 'Affirm', 'type' => 'api'],
        'sezzle' => ['name' => 'Sezzle', 'type' => 'api'],
        'paypal-checkout' => ['name' => 'PayPal Checkout', 'type' => 'api'],
        'applepay-us' => ['name' => 'Apple Pay US', 'type' => 'wallet'],
        'cashapp-mobile' => ['name' => 'Cash App Pay', 'type' => 'mobile'],
    ],

    'UY' => [
        'pagos-uy' => ['name' => 'Pagos Uruguay', 'type' => 'api'],
        'redpagos' => ['name' => 'Redpagos', 'type' => 'bank'],
        'abitab' => ['name' => 'Abitab', 'type' => 'bank'],
        'brou' => ['name' => 'BROU', 'type' => 'bank'],
        'banco-republica' => ['name' => 'Banco República', 'type' => 'bank'],
        'uy-pay' => ['name' => 'UruguayPay', 'type' => 'api'],
        'midinero' => ['name' => 'MiDinero', 'type' => 'mobile'],
        'itau-uy' => ['name' => 'Itaú Uruguay Mobile', 'type' => 'mobile'],
    ],

    'UZ' => [
        'payme-uz' => ['name' => 'Payme', 'type' => 'api'],
        'click-uz' => ['name' => 'Click', 'type' => 'api'],
        'apelsin' => ['name' => 'Apelsin', 'type' => 'api'],
        'uzum-bank-uz' => ['name' => 'Uzum Bank', 'type' => 'api'],
        'nbu-uz' => ['name' => 'National Bank of Uzbekistan', 'type' => 'bank'],
        'asaka' => ['name' => 'Asaka Bank', 'type' => 'bank'],
        'uzcard' => ['name' => 'UzCard', 'type' => 'card'],
        'humo' => ['name' => 'Humo', 'type' => 'card'],
        'beeline-uz' => ['name' => 'Beeline Uzbekistan', 'type' => 'mobile'],
        'uztelecom' => ['name' => 'Uztelecom', 'type' => 'mobile'],
        'oson' => ['name' => 'Oson', 'type' => 'wallet'],
    ],

    'VU' => [
        'nvb' => ['name' => 'National Bank of Vanuatu', 'type' => 'bank'],
        'vbv' => ['name' => 'Vanuatu Banking Corporation', 'type' => 'bank'],
        'tvl' => ['name' => 'TVL', 'type' => 'mobile'],
        'digicel-vu' => ['name' => 'Digicel Vanuatu', 'type' => 'mobile'],
    ],

    'VA' => [
        'ior' => ['name' => 'Istituto per le Opere di Religione', 'type' => 'bank'],
        'bpv' => ['name' => 'Banco Posta Vaticano', 'type' => 'bank'],
        'vatican-pay' => ['name' => 'VaticanPay', 'type' => 'api'],
    ],

    'VE' => [
        'pago-ve' => ['name' => 'Pago Venezuela', 'type' => 'api'],
        'mercantil' => ['name' => 'Banco Mercantil', 'type' => 'bank'],
        'banesco' => ['name' => 'Banesco', 'type' => 'bank'],
        'provincial' => ['name' => 'Banco Provincial', 'type' => 'bank'],
        'zelle-ve' => ['name' => 'Zelle Venezuela', 'type' => 'bank'],
        'airtm' => ['name' => 'Airtm', 'type' => 'mobile'],
        'billetera-ve' => ['name' => 'Billetera Móvil', 'type' => 'mobile'],
    ],

    'VN' => [
        'vnpay-vn' => ['name' => 'VNPay', 'type' => 'api'],
        'zalopay-vn' => ['name' => 'ZaloPay', 'type' => 'wallet'],
        'momo-vn-local' => ['name' => 'MoMo', 'type' => 'wallet'],
        'viettelpay-vn' => ['name' => 'ViettelPay', 'type' => 'wallet'],
        'payoo-vn' => ['name' => 'Payoo', 'type' => 'api'],
        'onepay-vn' => ['name' => 'OnePay', 'type' => 'api'],
        'vietcombank' => ['name' => 'Vietcombank', 'type' => 'bank'],
        'vietinbank' => ['name' => 'VietinBank', 'type' => 'bank'],
        'bidv' => ['name' => 'BIDV', 'type' => 'bank'],
        'vnmart' => ['name' => 'VNMART', 'type' => 'api'],
        'vnpay-qr' => ['name' => 'VNPay QR', 'type' => 'bank'],
    ],

    'YE' => [
        'cac-bank' => ['name' => 'CAC Bank', 'type' => 'bank'],
        'ib-yemen' => ['name' => 'International Bank of Yemen', 'type' => 'bank'],
        'yemen-cash' => ['name' => 'Yemen Cash', 'type' => 'mobile'],
        'sabafon' => ['name' => 'Sabafon', 'type' => 'mobile'],
        'mtn-ye' => ['name' => 'MTN Yemen', 'type' => 'mobile'],
    ],

    'ZM' => [
        'mtn-zm' => ['name' => 'MTN Mobile Money Zambia', 'type' => 'mobile'],
        'airtel-zm' => ['name' => 'Airtel Money Zambia', 'type' => 'mobile'],
        'zamtel-zm' => ['name' => 'Zamtel Kwacha', 'type' => 'mobile'],
        'zanaco' => ['name' => 'Zanaco', 'type' => 'bank'],
        'stanbic-zm' => ['name' => 'Stanbic Zambia', 'type' => 'bank'],
        'absa-zm' => ['name' => 'Absa Zambia', 'type' => 'bank'],
        'fineract' => ['name' => 'Fineract', 'type' => 'api'],
    ],

    'ZW' => [
        'ecocash-zw' => ['name' => 'EcoCash', 'type' => 'mobile'],
        'onemoney-zw' => ['name' => 'OneMoney', 'type' => 'mobile'],
        'cbz' => ['name' => 'CBZ', 'type' => 'bank'],
        'standard-chartered-zw' => ['name' => 'Standard Chartered Zimbabwe', 'type' => 'bank'],
        'nedbank-zw' => ['name' => 'Nedbank Zimbabwe', 'type' => 'bank'],
        'zipt' => ['name' => 'ZIPIT', 'type' => 'bank'],
        'paynow-zw' => ['name' => 'Paynow', 'type' => 'api'],
        'terrapay' => ['name' => 'TerraPay', 'type' => 'api'],
        'homelink' => ['name' => 'Homelink', 'type' => 'api'],
    ],

    'AI' => [
        'ang-corona' => ['name' => 'Anguilla Corona Bank', 'type' => 'bank'],
        'aib-ang' => ['name' => 'AIB Anguilla', 'type' => 'bank'],
        'anguilla-pay' => ['name' => 'Anguilla Pay', 'type' => 'api'],
    ],

    'CW' => [
        'maduro-cur' => ['name' => 'Maduro & Curiel\'s Bank', 'type' => 'bank'],
        'mcb-cw' => ['name' => 'MCB Curaçao', 'type' => 'bank'],
        'curacao-pay' => ['name' => 'Curaçao Pay', 'type' => 'api'],
    ],

    'GI' => [
        'sgj' => ['name' => 'Sovereign Bank Gibraltar', 'type' => 'bank'],
        'gi-bank' => ['name' => 'Gibraltar International Bank', 'type' => 'bank'],
        'gib-pay' => ['name' => 'GibraltarPay', 'type' => 'api'],
    ],

    'GL' => [
        'bank-of-greenland' => ['name' => 'Bank of Greenland', 'type' => 'bank'],
        'gronlandsbanken' => ['name' => 'Grønlandsbanken', 'type' => 'bank'],
        'gl-pay' => ['name' => 'GreenlandPay', 'type' => 'api'],
    ],

    'MO' => [
        'macaupass' => ['name' => 'Macau Pass', 'type' => 'api'],
        'bcm-mo' => ['name' => 'BCM Macau', 'type' => 'bank'],
        'bnu' => ['name' => 'BNU Macau', 'type' => 'bank'],
        'tai-fung' => ['name' => 'Tai Fung Bank', 'type' => 'bank'],
    ],

    'HK' => [
        'fps-hk' => ['name' => 'Faster Payment System', 'type' => 'bank'],
        'octopus' => ['name' => 'Octopus', 'type' => 'wallet'],
        'alipay-hk' => ['name' => 'AlipayHK', 'type' => 'wallet'],
        'tapgo' => ['name' => 'Tap & Go', 'type' => 'wallet'],
        'hsbc-hk' => ['name' => 'HSBC Hong Kong', 'type' => 'bank'],
        'hsbc-payme' => ['name' => 'PayMe', 'type' => 'wallet'],
        'wechat-hk' => ['name' => 'WeChat Pay HK', 'type' => 'wallet'],
        'boc-hk' => ['name' => 'Bank of China HK', 'type' => 'bank'],
    ],

    'TW' => [
        'line-pay-tw' => ['name' => 'LINE Pay Taiwan', 'type' => 'wallet'],
        'jkopay' => ['name' => 'JKO Pay', 'type' => 'wallet'],
        'pi-tw' => ['name' => 'Pi', 'type' => 'wallet'],
        'taiwan-pay' => ['name' => '台灣Pay', 'type' => 'bank'],
        'ecpay' => ['name' => 'ECPay', 'type' => 'api'],
        'newebpay' => ['name' => 'NewebPay', 'type' => 'api'],
        'line-pay-tw2' => ['name' => 'LINE Pay', 'type' => 'wallet'],
        'ctbc' => ['name' => 'CTBC Bank', 'type' => 'bank'],
        'chinatrust' => ['name' => 'Chinatrust', 'type' => 'bank'],
    ],

    'PR' => [
        'evertec' => ['name' => 'Evertec', 'type' => 'api'],
        'popular-pr' => ['name' => 'Banco Popular', 'type' => 'bank'],
        'firstbank-pr' => ['name' => 'FirstBank', 'type' => 'bank'],
        'oriental-pr' => ['name' => 'Oriental Bank', 'type' => 'bank'],
        'ath-movil' => ['name' => 'ATH Móvil', 'type' => 'wallet'],
        'pr-pay' => ['name' => 'Puerto Rico Pay', 'type' => 'api'],
    ],

    'GU' => [
        'bankofguam-gu' => ['name' => 'Bank of Guam', 'type' => 'bank'],
        'fcb-gu' => ['name' => 'First Commercial Bank Guam', 'type' => 'bank'],
        'guam-pay' => ['name' => 'GuamPay', 'type' => 'api'],
    ],

    'AX' => [
        'alandsbanken' => ['name' => 'Ålandsbanken', 'type' => 'bank'],
        'ax-pay' => ['name' => 'ÅlandPay', 'type' => 'api'],
    ],

    'FO' => [
        'ffb' => ['name' => 'Føroya Banki', 'type' => 'bank'],
        'betri' => ['name' => 'Betri Banki', 'type' => 'bank'],
        'fo-pay' => ['name' => 'FaroePay', 'type' => 'api'],
    ],

    'IM' => [
        'iom-bank' => ['name' => 'Isle of Man Bank', 'type' => 'bank'],
        'iom-pay' => ['name' => 'Isle of Man Pay', 'type' => 'api'],
    ],

    'GG' => [
        'guernsey-bank' => ['name' => 'Guernsey Banking', 'type' => 'bank'],
        'gg-pay' => ['name' => 'GuernseyPay', 'type' => 'api'],
    ],

    'JE' => [
        'jersey-bank' => ['name' => 'Jersey Bank', 'type' => 'bank'],
        'je-pay' => ['name' => 'JerseyPay', 'type' => 'api'],
    ],

    'PF' => [
        'banque-de-polynesie' => ['name' => 'Banque de Polynésie', 'type' => 'bank'],
        'socredo' => ['name' => 'SOCREDO', 'type' => 'bank'],
        'pf-pay' => ['name' => 'PolynesiaPay', 'type' => 'api'],
    ],

    'NC' => [
        'bnc-nc' => ['name' => 'BNC Nouvelle-Calédonie', 'type' => 'bank'],
        'socredo-nc' => ['name' => 'SOCREDO', 'type' => 'bank'],
        'nc-pay' => ['name' => 'New Caledonia Pay', 'type' => 'api'],
    ],

    'YT' => [
        'bim-yt' => ['name' => 'BIM Mayotte', 'type' => 'bank'],
        'yt-pay' => ['name' => 'Mayotte Pay', 'type' => 'api'],
    ],

    'RE' => [
        'bcr-re' => ['name' => 'Banque de la Réunion', 'type' => 'bank'],
        're-pay' => ['name' => 'RéunionPay', 'type' => 'api'],
    ],

    'MQ' => [
        'bqm' => ['name' => 'BQM Martinique', 'type' => 'bank'],
        'mq-pay' => ['name' => 'Martinique Pay', 'type' => 'api'],
    ],

    'GP' => [
        'bgp' => ['name' => 'Banque des Antilles', 'type' => 'bank'],
        'gp-pay' => ['name' => 'Guadeloupe Pay', 'type' => 'api'],
    ],

    'BL' => [
        'banque-st-barth' => ['name' => 'Banque St Barth', 'type' => 'bank'],
        'bl-pay' => ['name' => 'St Barth Pay', 'type' => 'api'],
    ],

    'MF' => [
        'smx' => ['name' => 'Semiramis', 'type' => 'bank'],
        'mf-pay' => ['name' => 'St Martin Pay', 'type' => 'api'],
    ],

    'PM' => [
        'spm-bank' => ['name' => 'St Pierre Bank', 'type' => 'bank'],
        'pm-pay' => ['name' => 'St Pierre Pay', 'type' => 'api'],
    ],

    'WF' => [
        'btwf' => ['name' => 'Bank of Wallis & Futuna', 'type' => 'bank'],
        'wf-pay' => ['name' => 'WallisPay', 'type' => 'api'],
    ],

    'TF' => [
        'taaf-pay' => ['name' => 'TAAF Pay', 'type' => 'api'],
    ],

    'BV' => [
        'bv-pay' => ['name' => 'BouvetPay', 'type' => 'api'],
    ],

    'GS' => [
        'gs-pay' => ['name' => 'South Georgia Pay', 'type' => 'api'],
    ],

    'IO' => [
        'io-pay' => ['name' => 'BIOT Pay', 'type' => 'api'],
    ],

    'CX' => [
        'cx-pay' => ['name' => 'Christmas Island Pay', 'type' => 'api'],
    ],

    'CC' => [
        'cc-pay' => ['name' => 'Cocos Islands Pay', 'type' => 'api'],
    ],

    'NF' => [
        'nf-pay' => ['name' => 'Norfolk Island Pay', 'type' => 'api'],
    ],

    'UM' => [
        'um-pay' => ['name' => 'US Minor Outlying Pay', 'type' => 'api'],
    ],

    'EH' => [
        'eh-pay' => ['name' => 'Western Sahara Pay', 'type' => 'api'],
    ],

    'AQ' => [
        'aq-pay' => ['name' => 'Antarctic Pay', 'type' => 'api'],
    ],
];
