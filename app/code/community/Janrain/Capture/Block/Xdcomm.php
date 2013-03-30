<?php

class Janrain_Capture_Block_Xdcomm extends Mage_Core_Block_Abstract {

    protected function _toHtml() {
        $html = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
  <title>Cross-Domain Receiver Page</title>
</head>
<body>

<script src="http://cdn.janraincapture.com/js/lib/xdcomm.js"
        type="text/javascript"></script>

</body>
</html>
';

        return $html;
    }

}