import { Elysia } from "elysia";
import { healthRoutes } from "./modules/health/routes/health.routes";
import { authRoutes } from "./modules/auth/routes/auth.routes";
import { branchRoutes } from "./modules/branch/routes/branch.routes";
import { contentRoutes } from "./modules/content/routes/content.routes";
import { addressRoutes } from "./modules/address/routes/address.routes";
import { memberRoutes } from "./modules/member/routes/member.routes";
import { mediaRoutes } from "./modules/media/routes/media.routes";
import { rewardRoutes } from "./modules/reward/routes/reward.routes";
import { redeemRoutes } from "./modules/redeem/routes/redeem.routes";
import { otpRoutes } from "./modules/otp/routes/otp.routes";
import { deviceRoutes } from "./modules/device/routes/device.routes";
import { tierRoutes } from "./modules/tier/routes/tier.routes";
import { promotionBannerRoutes } from "./modules/promotion-banner/routes/promotion-banner.routes";
import { faqRoutes } from "./modules/faq/routes/faq.routes";
import { pointLedgerRoutes } from "./modules/point-ledger/routes/point-ledger.routes";

const app = new Elysia()
  .get("/", () => "Hello Elysia")
  .use(healthRoutes)
  .use(authRoutes)
  .use(branchRoutes)
  .use(contentRoutes)
  .use(addressRoutes)
  .use(memberRoutes)
  .use(mediaRoutes)
  .use(rewardRoutes)
  .use(redeemRoutes)
  .use(otpRoutes)
  .use(deviceRoutes)
  .use(tierRoutes)
  .use(promotionBannerRoutes)
  .use(faqRoutes)
  .use(pointLedgerRoutes)
  .listen({
    port: 3000,
    hostname: '0.0.0.0'
  });

console.log(
  `🦊 Elysia is running at ${app.server?.hostname}:${app.server?.port}`
);
