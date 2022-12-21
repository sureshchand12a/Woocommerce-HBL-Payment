<style>
    .hbl_payment_form{
        display:flex;
        justify-content:space-between;
    }

    .hbl_payment_form .payment_form,
    .hbl_payment_form .order_review{
        width:48%;
    }

    .hbl_payment_form img{
        max-width:100%;
        height:auto;
    }

    .hbl_payment_form input[type=submit]{
        background: red;
        color: #fff;
        border: none;
        width: 100%;
        padding: 10px;
        text-transform: uppercase;
        font-weight: bold;
    }
</style>

<div class="hbl_payment_form">
    <div class="payment_form">
        <img src="https://cellpay.com.np/storage/app/uploads/public/5de/5e9/33e/5de5e933e73ea932481111.png" alt="Himalayan Bank Payment">

        <form action="<?php echo $this->endpoint; ?>" method="post">
            <?php
                $purchaseitems = $paymentfields['purchaseItems'];
                unset($paymentfields['purchaseItems']);
                foreach($paymentfields as $k => $f){
                    if( is_array( $f ) ){
                        foreach( $f as $kk => $ff ){
                            if( is_array( $ff ) ){
                                foreach( $ff as $kkk => $fff ){
                                    echo sprintf('<input type="hidden" name="%s[%s]" value="%s" />', $k, $kk, $fff);
                                }
                            }else{
                                echo sprintf('<input type="hidden" name="%s[%s]" value="%s" />', $k, $kk, $ff);
                            }
                        }
                    }else{
                        echo sprintf('<input type="hidden" name="%s" value="%s" />', $k, $f);
                    }
                }

                //purchase items
                foreach($purchaseitems[0] as $k => $product){
                    if( is_array( $product ) ){
                        foreach( $product as $pk => $pv ){
                            echo sprintf('<input type="hidden" name="purchaseItems[0][%s][%s]" value="%s" />', $k, $pk, $pv);
                        }
                    }else{
                        echo sprintf('<input type="hidden" name="purchaseItems[0][%s]" value="%s" />', $k, $product);
                    }
                }
            ?>
            <input type="submit" value="Pay by HBL Bank">
        </form>
    </div>
    <div class="order_review">
        <?php
            wc_get_template( 'checkout/review-order.php', array( 'checkout' => WC()->checkout() ) );
        ?>
    </div>
</div>