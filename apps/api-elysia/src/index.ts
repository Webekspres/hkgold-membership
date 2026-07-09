import { Elysia } from "elysia";
import { healthRoutes } from "./modules/health/routes/health.routes";
import { authRoutes } from "./modules/auth/routes/auth.routes";

const app = new Elysia()
  .get("/", () => "Hello Elysia")
  .use(healthRoutes)
  .use(authRoutes)
  .listen({
    port: 3000,
    hostname: '0.0.0.0'
  });

console.log(
  `🦊 Elysia is running at ${app.server?.hostname}:${app.server?.port}`
);
