<?php
use aqua\App;
?>
<div style="padding: 10px 14px 10px 2px;" >
Running applet links or post method do not navigate away from the current page.
It is done by $_GET params or maintaining states.
<br/>
<br/>
Have a global applet controller, and simply place the views into site or zone folder to customize!
<?php App::anchor('tutorial/applet', 'Read more') ?>
</div>