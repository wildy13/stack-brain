<?php
if (!defined('InternalAccess')) exit('{"Status": 0, "ErrorCode": "403", "ErrorMessage": "403"}');
if ($Result) :
?>{
"Status": 1
}
<?php else : ?>{
"Status": 0,
"ErrorCode": 1,
"ErrorMessage": "Failed"
}
<?php endif; ?>