// ==========================================
// USE AUTH HOOK
// Hook para autenticação
// ==========================================

import { useAuthContext } from '../providers/AuthProvider';

/**
 * Hook para acessar autenticação
 */
export function useAuth() {
  return useAuthContext();
}
