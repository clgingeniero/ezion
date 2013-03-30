<?php

class Janrain_Capture_Block_Refresh extends Mage_Core_Block_Abstract {

    protected function _toHtml() {
        $html = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

    <head>
    </head>

    <body>

    <p>Please wait...</p>
    <script type="text/javascript">
        if (window.location != window.parent.location)
            window.parent.location.reload();
        else
            window.location = "' . Mage::getUrl() . '";
    </script>

    </body>

</html>
';

        return $html;
    }

}