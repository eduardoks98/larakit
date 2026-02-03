// ==========================================
// AUTH TYPES - Autenticação com games-admin
// ==========================================

/**
 * Usuário autenticado (retornado pelo games-admin)
 */
export interface AuthUser {
  /** ID único (UUID) */
  id: string;

  /** ID do usuário no games-admin */
  gameUserId: string;

  /** Email */
  email: string;

  /** Username único */
  username: string;

  /** Nickname para exibição */
  nickname: string;

  /** URL do avatar */
  avatarUrl?: string;

  /** Providers OAuth vinculados */
  providers: AuthProvider[];

  /** Data de criação */
  createdAt: string;
}

/**
 * Providers de autenticação disponíveis
 */
export type AuthProvider = 'google' | 'facebook' | 'discord' | 'email';

/**
 * Token JWT decodificado
 */
export interface DecodedToken {
  /** Subject (user ID) */
  sub: string;

  /** Código do jogo */
  game: string;

  /** Tipo de token */
  type?: 'user' | 'admin' | 'sponsor';

  /** Issued at */
  iat: number;

  /** Expiration */
  exp: number;
}

/**
 * Resultado de validação de token
 */
export interface TokenValidationResult {
  /** Se o token é válido */
  valid: boolean;

  /** Usuário (se válido) */
  user?: AuthUser;

  /** Erro (se inválido) */
  error?: string;
}

/**
 * Configuração de autenticação
 */
export interface AuthConfig {
  /** URL do games-admin */
  gamesAdminUrl: string;

  /** Código do jogo */
  gameCode: string;

  /** Secret para validar JWT */
  jwtSecret: string;

  /** Tempo de expiração do token em segundos */
  tokenExpiration?: number;
}

/**
 * Resposta de login do games-admin
 */
export interface LoginResponse {
  /** Token JWT */
  token: string;

  /** Usuário */
  user: AuthUser;

  /** Expiração do token */
  expiresAt: string;
}

/**
 * Estado de autenticação no cliente
 */
export interface AuthState {
  /** Se está autenticado */
  isAuthenticated: boolean;

  /** Se está carregando */
  isLoading: boolean;

  /** Usuário atual */
  user: AuthUser | null;

  /** Token atual */
  token: string | null;

  /** Erro de autenticação */
  error: string | null;
}

/**
 * Ações de autenticação
 */
export interface AuthActions {
  /** Login com provider OAuth */
  loginWithProvider: (provider: AuthProvider) => void;

  /** Login com email/senha */
  loginWithEmail: (email: string, password: string) => Promise<void>;

  /** Registrar com email */
  register: (email: string, password: string, username: string) => Promise<void>;

  /** Logout */
  logout: () => Promise<void>;

  /** Atualizar nickname */
  updateNickname: (nickname: string) => Promise<void>;

  /** Validar token atual */
  validateToken: () => Promise<boolean>;
}
