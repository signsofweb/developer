<?php

class Imaginato_Criteo_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_criteoActive = 'criteo/setting/enabled';
    protected $_criteoAccount = 'criteo/setting/account';

    public function isActive()
    {
        return Mage::getStoreConfigFlag($this->_criteoActive);
    }

    public function getAccount()
    {
        $account = trim(Mage::getStoreConfig($this->_criteoAccount));

        return $account ? : '';
    }

    public function isMobile()
    {
        $mobile = 0;
        $useragent=$_SERVER['HTTP_USER_AGENT'];
        if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
        {
            $mobile = 1;
        }
        return $mobile;
    }
    public function isTablet()
    {
        $tablet = 0;
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $tabletDevices = array(
            'BlackBerryTablet' => 'PlayBook|RIM Tablet',
            'iPad' => 'iPad|iPad.*Mobile', // @todo: check for mobile friendly emails topic.
            'NexusTablet' => '^.*Android.*Nexus(((?:(?!Mobile))|(?:(\s(7|10).+))).)*$',
            // @reference: http://www.labnol.org/software/kindle-user-agent-string/20378/
            'Kindle' => 'Kindle|Silk.*Accelerated',
            'SamsungTablet' => 'SAMSUNG.*Tablet|Galaxy.*Tab|GT-P1000|GT-P1010|GT-P6210|GT-P6800|GT-P6810|GT-P7100|GT-P7300|GT-P7310|GT-P7500|GT-P7510|SCH-I800|SCH-I815|SCH-I905|SGH-I957|SGH-I987|SGH-T849|SGH-T859|SGH-T869|SPH-P100|GT-P3100|GT-P3110|GT-P5100|GT-P5110|GT-P6200|GT-P7320|GT-P7511|GT-N8000|GT-P8510|SGH-I497|SPH-P500|SGH-T779|SCH-I705|SCH-I915|GT-N8013|GT-P3113|GT-P5113|GT-P8110|GT-N8010|GT-N8005|GT-N8020|GT-P1013|GT-P6201|GT-P6810|GT-P7501',
            'HTCtablet' => 'HTC Flyer|HTC Jetstream|HTC-P715a|HTC EVO View 4G|PG41200',
            'MotorolaTablet' => 'xoom|sholest|MZ615|MZ605|MZ505|MZ601|MZ602|MZ603|MZ604|MZ606|MZ607|MZ608|MZ609|MZ615|MZ616|MZ617',
            'AsusTablet' => 'Transformer|TF101',
            'NookTablet' => 'Android.*Nook|NookColor|nook browser|BNTV250A|LogicPD Zoom2',
            // @ref: http://www.acer.ro/ac/ro/RO/content/drivers
            // @ref: http://www.packardbell.co.uk/pb/en/GB/content/download (Packard Bell is part of Acer)
            'AcerTablet' => 'Android.*\b(A100|A101|A200|A500|A501|A510|A700|A701|W500|W500P|W501|W501P|G100|G100W)\b',
            // @ref: http://eu.computers.toshiba-europe.com/innovation/family/Tablets/1098744/banner_id/tablet_footerlink/
            // @ref: http://us.toshiba.com/tablets/tablet-finder
            // @ref: http://www.toshiba.co.jp/regza/tablet/
            'ToshibaTablet' => 'Android.*(AT100|AT105|AT200|AT205|AT270|AT275|AT300|AT305|AT1S5|AT500|AT570|AT700|AT830)',
            'YarvikTablet' => 'Android.*(TAB210|TAB211|TAB224|TAB250|TAB260|TAB264|TAB310|TAB360|TAB364|TAB410|TAB411|TAB420|TAB424|TAB450|TAB460|TAB461|TAB464|TAB465|TAB467|TAB468)',
            'MedionTablet' => 'Android.*\bOYO\b|LIFE.*(P9212|P9514|P9516|S9512)|LIFETAB',
            'ArnovaTablet' => 'AN10G2|AN7bG3|AN7fG3|AN8G3|AN8cG3|AN7G3|AN9G3|AN7dG3|AN7dG3ST|AN7dG3ChildPad|AN10bG3|AN10bG3DT',
            // @reference: http://wiki.archosfans.com/index.php?title=Main_Page
            'ArchosTablet' => 'Android.*ARCHOS|101G9|80G9',
            // @reference: http://en.wikipedia.org/wiki/NOVO7
            'AinolTablet' => 'NOVO7|Novo7Aurora|Novo7Basic|NOVO7PALADIN',
            // @todo: inspect http://esupport.sony.com/US/p/select-system.pl?DIRECTOR=DRIVER
            'SonyTablet' => 'Sony Tablet|Sony Tablet S',
            // @ref: db + http://www.cube-tablet.com/buy-products.html
            'CubeTablet' => 'Android.*(K8GT|U9GT|U10GT|U16GT|U17GT|U18GT|U19GT|U20GT|U23GT|U30GT)',
            // @ref: http://www.cobyusa.com/?p=pcat&pcat_id=3001
            'CobyTablet' => 'MID1042|MID1045|MID1125|MID1126|MID7012|MID7014|MID7034|MID7035|MID7036|MID7042|MID7048|MID7127|MID8042|MID8048|MID8127|MID9042|MID9740|MID9742|MID7022|MID7010',
            // @ref: http://pdadb.net/index.php?m=pdalist&list=SMiT (NoName Chinese Tablets)
            // @ref: http://www.imp3.net/14/show.php?itemid=20454
            'SMiTTablet' => 'Android.*(\bMID\b|MID-560|MTV-T1200|MTV-PND531|MTV-P1101|MTV-PND530)',
            // @ref: http://www.rock-chips.com/index.php?do=prod&pid=2
            'RockChipTablet' => 'Android.*(RK2818|RK2808A|RK2918|RK3066)|RK2738|RK2808A',
            // @ref: http://www.telstra.com.au/home-phone/thub-2/
            'TelstraTablet' => 'T-Hub2',
            // @ref: http://www.fly-phone.com/devices/tablets/ ; http://www.fly-phone.com/service/
            'FlyTablet' => 'IQ310|Fly Vision',
            // @ref: http://www.bqreaders.com/gb/tablets-prices-sale.html
            'bqTablet' => 'bq.*(Elcano|Curie|Edison|Maxwell|Kepler|Pascal|Tesla|Hypatia|Platon|Newton|Livingstone|Cervantes|Avant)',
            // @ref: http://www.huaweidevice.com/worldwide/productFamily.do?method=index&directoryId=5011&treeId=3290
            // @ref: http://www.huaweidevice.com/worldwide/downloadCenter.do?method=index&directoryId=3372&treeId=0&tb=1&type=software (including legacy tablets)
            'HuaweiTablet' => 'MediaPad|IDEOS S7|S7-201c|S7-202u|S7-101|S7-103|S7-104|S7-105|S7-106|S7-201|S7-Slim',
            'GenericTablet' => 'Android.*\b97D\b|Tablet(?!.*PC)|ViewPad7|LG-V909|MID7015|BNTV250A|LogicPD Zoom2|\bA7EB\b|CatNova8|A1_07|CT704|CT1002|\bM721\b|hp-tablet',
        );

        foreach ($tabletDevices as $_regex) {
            $regex = str_replace('/', '\/', $_regex);
            if (preg_match('/' . $regex . '/is', $userAgent)) {
                $tablet = 1;
                break;
            }
        }
        return $tablet;
    }
}