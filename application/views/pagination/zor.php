<div>
    <?php
        $sector = 5;
        $total_pages = $total_pages;

    ?>

    <ul class="pagination ">

        <?php if ($previous_page){ ?>
            <li class="page-item"><a class="page-link" href="<?php echo str_replace('{page}', $previous_page.'/', $url) ?>">← Предыдущая</a></li>
        <?php } ?>




<?php	if ($current_page > $sector): ?>
    <li class="page-item "><a class="page-link" href="<?php echo str_replace('{page}', 1, $url) ?>/">1</a></li>
<?php		if ($current_page != $sector+1):?>
        <li class="page-item"><a class="page-link" href="#">...</a></li>
    <?php		endif;?>
<?php	endif; ?>
            <?php	for ($i = $current_page - ($sector - 1), $stop = $current_page + $sector; $i < $stop; ++$i): ?>
            <?php		if ($i < 1 OR $i > $total_pages) continue;?>
            <?php		if($i>=10 and empty($utendset)):
            $utendset = true;?>


        <?php		endif;?>
        <?php		if ($i == $current_page): ?>
            <li class="page-item active"><a class="page-link" href="<?php echo str_replace('{page}', $i.'/', $url) ?>"><?php echo $i ?></a></li>

        <?php		else:?>
        <li class="page-item"><a class="page-link" href="<?php echo str_replace('{page}', $i.'/', $url) ?>"><?php echo $i ?></a></li>
        <?php		endif;?>
        <?php	endfor?>
        <?php	if(empty($utendset)):
            $utendset = true?>

        <?php	endif;?>
        <?php	if ($current_page <= $total_pages - $sector): ?>

        <?php if ($current_page != $total_pages - $sector):?>

        <li class="page-item"><a class="page-link" href="#">...</a></li>

            <?php endif;?>
        <li class="page-item"><a class="page-link" href="<?php echo str_replace('{page}', $total_pages.'/', $url) ?>"><?php echo $total_pages ?></a></li>
        <?php	endif ?>

        <?php if ($next_page and $next_page <= $total_pages){ ?>
            <li class="page-item"><a class="page-link" href="<?php echo str_replace('{page}', $next_page.'/', $url) ?>">Следующая →</a></li>
        <?php } ?>
    </ul>
</div>