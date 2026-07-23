import * as Clipboard from "expo-clipboard";

import { toast } from "@/lib/sonner";

export async function copyMemberCode(memberCode: string) {
  await Clipboard.setStringAsync(memberCode);
  toast.success("Kode member berhasil disalin", {
    // description: memberCode,
    duration: 3500,
    closeButton: true,
  });
}
