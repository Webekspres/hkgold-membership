import { Elysia } from "elysia";
import { healthRoutes } from "./modules/health/routes/health.routes";
import { authRoutes } from "./modules/auth/routes/auth.routes";
import { branchRoutes } from "./modules/branch/routes/branch.routes";
import { contentRoutes } from "./modules/content/routes/content.routes";

const app = new Elysia()
  .get("/", () => "Hello Elysia")
  .use(healthRoutes)
  .use(authRoutes)
  .use(branchRoutes)
  .use(contentRoutes)
  .listen({
    port: 3000,
    hostname: '0.0.0.0'
  });

console.log(
  `🦊 Elysia is running at ${app.server?.hostname}:${app.server?.port}`
);
