<?php
/*******************************************************************************************************
 * Copyright (c) 2019.                                                                                 *
 * All Rights reserved by Haren Sarma on Behalf of Infybot                                             *
 * No part of this file, project allowed to modify, reuse and/or do reverse engineering.               *
 * This is a private project protected under copyright law.                                            *
 *******************************************************************************************************/

return array(
    'income_names'        => array('Referral Income' => 'Direct Referral Income', 'Level Income' => 'Level Income', 'ROI'=>'ROI'),
    #Matching Income, Referral Income, Level Income, ROI, Non Working Level Income, Single Leg Income
    'wallet_types'        => array('Default'),
    # Name of available wallets. First wallet is required and all withdrawal will happen from this wallet only.
    'transfer_ban_wallet' => array(''),
    # Enter wallet names (same as wallet_types) where inter wallet transfer is not possible.
    'repurchase_wallet'   => 'Default',
    # Enter wallet name from where user can use funds to buy products.
);
