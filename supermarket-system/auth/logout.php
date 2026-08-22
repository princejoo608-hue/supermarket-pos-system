<?php
session_start();
session_destroy();
header("Location: /supermarket-system/auth/login.php");
exit();
