<?php

namespace TikTokShop\Enums;

/**
 * Enum para os status de produto aceitos pela API TikTok Shop.
 *
 * @see https://partner.tiktokshop.com/docv2/page/search-products-202502
 */
enum ProductStatus: string
{
    case ALL                 = 'ALL';
    case DRAFT               = 'DRAFT';
    case PENDING             = 'PENDING';
    case FAILED              = 'FAILED';
    case ACTIVATE            = 'ACTIVATE';
    case SELLER_DEACTIVATED  = 'SELLER_DEACTIVATED';
    case PLATFORM_DEACTIVATED= 'PLATFORM_DEACTIVATED';
    case FREEZE              = 'FREEZE';
    case DELETED             = 'DELETED';
}
