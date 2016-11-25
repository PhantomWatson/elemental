<p>
    <?php
        echo $this->Html->link(
            ($expiration ? 'Renew' : 'Purchase').' access for $'.number_format($cost, 2),
            '#',
            array(
                'class' => 'btn btn-primary btn-large',
                'id' => 'purchase_classroom_module'
            )
        );
        $this->Html->script('https://checkout.stripe.com/checkout.js', array('inline' => false));
        $this->Html->script('purchase.js', array('inline' => false));
        $this->Js->buffer("
            elementalPurchase.setupPurchaseButton({
                button_selector: '#purchase_classroom_module',
                confirmation_message: 'Confirm payment of $".number_format($cost, 2)." for the Elemental Classroom Module?',
                cost_dollars: $cost,
                description: 'Classroom Module (\${$cost})',
                key: '".Configure::read('Stripe.Public')."',
                post_data: {
                    instructor_id: '$user_id'
                },
                post_url: '/purchases/complete_purchase/classroom_module',
                email: '$email'
            });
        ");
    ?>
</p>