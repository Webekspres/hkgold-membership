import { useCallback, useEffect, useState } from 'react';

import {
  getAccessToken,
  getStoredMember,
  getStoredUser,
  login as loginRequest,
  logout as logoutRequest,
  register as registerRequest,
} from '@/services/auth';
import type { AuthMember, AuthResponse, AuthUser } from '@/types/auth';

type RegisterInput = {
  email: string;
  fullName: string;
  phoneNumber: string;
  password: string;
};

export function useAuth() {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [member, setMember] = useState<AuthMember | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;

    void (async () => {
      const [storedUser, storedMember, token] = await Promise.all([
        getStoredUser(),
        getStoredMember(),
        getAccessToken(),
      ]);

      if (cancelled) return;

      if (token) {
        setUser(storedUser);
        setMember(storedMember);
      } else {
        setUser(null);
        setMember(null);
      }
      setIsLoading(false);
    })();

    return () => {
      cancelled = true;
    };
  }, []);

  const applySession = useCallback((data: AuthResponse) => {
    setUser(data.user);
    setMember(data.member);
  }, []);

  const login = useCallback(
    async (identifier: string, password: string) => {
      const data = await loginRequest(identifier, password);
      applySession(data);
      return data;
    },
    [applySession]
  );

  const register = useCallback(
    async (input: RegisterInput) => {
      const data = await registerRequest(input);
      applySession(data);
      return data;
    },
    [applySession]
  );

  const logout = useCallback(async () => {
    await logoutRequest();
    setUser(null);
    setMember(null);
  }, []);

  return {
    user,
    member,
    isLoading,
    isAuthenticated: !!user,
    login,
    register,
    logout,
  };
}
