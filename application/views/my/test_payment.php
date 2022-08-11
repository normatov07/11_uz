<!DOCTYPE html>
<html>
<head>
    <title>Test Payment</title>
    <meat http-equiv="content-type" content="text/html;charset=utf-8"/>
</head>
<body>
<form method="post" name="test_payment_form" action="/test/request_payment">
<?foreach($params as $param_name=>$param_value):?>
    <input type="hidden" name="<?=$param_name?>" value="<?=$param_value?>"/>
<?endforeach?>
    Укажите количество бонусов, которое вы желаете приобрести:
    <input type="text" name="amount" value="0"/>
    <input type="submit" value="Купить"/>
</form>
</body>
</html>