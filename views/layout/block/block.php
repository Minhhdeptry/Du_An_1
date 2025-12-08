<?php
$__blocks = [];
$__currentBlock = null;

function startblock($name)
{
    global $__currentBlock;
    $__currentBlock = $name;
    ob_start();
}

function endblock()
{
    global $__blocks, $__currentBlock;
    $__blocks[$__currentBlock] = ob_get_clean();
}

function block($name)
{
    global $__blocks;
    echo $__blocks[$name] ?? '';
}
