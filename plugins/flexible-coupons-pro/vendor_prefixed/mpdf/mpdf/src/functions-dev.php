<?php

namespace FlexibleCouponsProVendor;

if (!\function_exists('FlexibleCouponsProVendor\dd')) {
    function dd(...$args)
    {
        if (\function_exists('FlexibleCouponsProVendor\dump')) {
            dump(...$args);
        } else {
            \var_dump(...$args);
        }
        die;
    }
}
