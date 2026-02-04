export interface CreateGameOptions {
    projectName: string;
    gameCode: string;
    gameName: string;
    gamesAdminUrl: string;
    maxPlayers: number;
    includeBot: boolean;
}
export declare function createGame(options: CreateGameOptions): Promise<void>;
//# sourceMappingURL=index.d.ts.map