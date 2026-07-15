<?php

declare(strict_types=1);

namespace App\Enums;

enum ActivityLogAction: string
{
    case ManualPointInjection = 'manual_point_injection';
    case BulkPointInjection = 'bulk_point_injection';
    case PointAnnualArchive = 'point_annual_archive';
    case MemberCreated = 'member_created';
    case MemberUpdated = 'member_updated';
    case StaffCreated = 'staff_created';
    case StaffUpdated = 'staff_updated';
    case RewardCreated = 'reward_created';
    case RewardUpdated = 'reward_updated';
    case RewardStockUpdated = 'reward_stock_updated';
    case BranchCreated = 'branch_created';
    case BranchUpdated = 'branch_updated';
    case ContentCreated = 'content_created';
    case ContentUpdated = 'content_updated';
    case TierConfigUpdated = 'tier_config_updated';
    case PromotionBannerUpdated = 'promotion_banner_updated';
    case RedeemConfirmation = 'redeem_confirmation';
    case RedeemTokenExpiredRelease = 'redeem_token_expired_release';

    public function label(): string
    {
        return match ($this) {
            self::ManualPointInjection => 'Suntik Poin Manual',
            self::BulkPointInjection => 'Injeksi Poin Massal',
            self::PointAnnualArchive => 'Arsip Poin Tahunan',
            self::MemberCreated => 'Anggota Dibuat',
            self::MemberUpdated => 'Anggota Diperbarui',
            self::StaffCreated => 'Staff Dibuat',
            self::StaffUpdated => 'Staff Diperbarui',
            self::RewardCreated => 'Reward Dibuat',
            self::RewardUpdated => 'Reward Diperbarui',
            self::RewardStockUpdated => 'Stok Reward Diperbarui',
            self::BranchCreated => 'Cabang Dibuat',
            self::BranchUpdated => 'Cabang Diperbarui',
            self::ContentCreated => 'Konten Dibuat',
            self::ContentUpdated => 'Konten Diperbarui',
            self::TierConfigUpdated => 'Konfigurasi Tier Diperbarui',
            self::PromotionBannerUpdated => 'Banner Promosi Diperbarui',
            self::RedeemConfirmation => 'Konfirmasi Redeem Poin',
            self::RedeemTokenExpiredRelease => 'Rilis Token Redeem Kedaluwarsa',
        };
    }
}
