import { Image } from "expo-image";
import { LinearGradient } from "expo-linear-gradient";
import { router } from "expo-router";
import { Pressable, StyleSheet, View } from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Text } from "@/components/ui/text";

export default function LoginScreen() {
  return (
    <View style={styles.container}>
      <Image
        source={require("@/assets/media/background.webp")}
        style={styles.background}
        contentFit="cover"
      />
      <SafeAreaView style={styles.overlay}>
        <Card className="w-full max-w-md border-stone-200 bg-stone-50 shadow-md shadow-stone-900/10">
          <CardHeader className="items-center gap-4">
            <Image
              source={require("@/assets/logo/logo-hkgold.webp")}
              style={styles.logo}
              contentFit="contain"
            />
            <CardTitle className="text-lg text-stone-600">Masuk</CardTitle>
          </CardHeader>
          <CardContent className="gap-4">
            <View className="gap-1.5">
              <Text variant="small" className="text-stone-600">
                Email
              </Text>
              <Input
                className="h-11 rounded-lg border-stone-300 bg-white text-stone-700 native:placeholder:text-stone-400 web:placeholder:text-stone-400 focus-visible:border-stone-400 focus-visible:ring-stone-300/40"
                placeholder="email@example.com"
                placeholderTextColor="#a8a29e"
                keyboardType="email-address"
                autoCapitalize="none"
                autoComplete="email"
              />
            </View>
            <View className="gap-1.5">
              <Text variant="small" className="text-stone-600">
                Password
              </Text>
              <Input
                className="h-11 rounded-lg border-stone-300 bg-white text-stone-700 native:placeholder:text-stone-400 web:placeholder:text-stone-400 focus-visible:border-stone-400 focus-visible:ring-stone-300/40"
                placeholder="Password"
                placeholderTextColor="#a8a29e"
                secureTextEntry
                autoComplete="password"
              />
            </View>

            <Pressable
              className="active:opacity-90"
              onPress={() => router.replace("/(tabs)")}
            >
              <LinearGradient
                colors={["#f5c842", "#e8a020"]}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 1 }}
                style={{
                  borderRadius: 6,
                  paddingVertical: 10,
                  paddingHorizontal: 16,
                  alignItems: "center",
                  borderWidth: 0,
                }}
              >
                <Text className="font-semibold text-stone-800">Masuk</Text>
              </LinearGradient>
            </Pressable>
          </CardContent>
        </Card>
      </SafeAreaView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "transparent",
  },
  background: {
    position: "absolute",
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
  },
  logo: {
    width: 192,
    height: 80,
    alignSelf: "center",
  },
  overlay: {
    flex: 1,
    alignItems: "center",
    justifyContent: "center",
    paddingHorizontal: 24,
    backgroundColor: "transparent",
  },
});
