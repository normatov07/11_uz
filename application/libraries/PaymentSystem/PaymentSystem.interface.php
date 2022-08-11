<?php

interface Payment {

    function getSign($order);

    function generateForm($order);

    function verifyResponse();
}
