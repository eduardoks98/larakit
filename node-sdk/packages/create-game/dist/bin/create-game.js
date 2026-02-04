#!/usr/bin/env node
"use strict";
// ==========================================
// CREATE GAME CLI
// CLI para criar novos jogos MySys
// ==========================================
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const index_1 = require("../index");
const chalk_1 = __importDefault(require("chalk"));
const inquirer_1 = __importDefault(require("inquirer"));
async function main() {
    console.log(chalk_1.default.cyan('\n  MySys Game SDK - Create Game\n'));
    // Pegar nome do projeto dos argumentos ou perguntar
    let projectName = process.argv[2];
    if (!projectName) {
        const answers = await inquirer_1.default.prompt([
            {
                type: 'input',
                name: 'projectName',
                message: 'Nome do projeto:',
                default: 'my-game',
                validate: (input) => {
                    if (!input.trim())
                        return 'Nome é obrigatório';
                    if (!/^[a-z0-9-]+$/.test(input))
                        return 'Use apenas letras minúsculas, números e hífens';
                    return true;
                },
            },
        ]);
        projectName = answers.projectName;
    }
    // Perguntar configurações
    const config = await inquirer_1.default.prompt([
        {
            type: 'input',
            name: 'gameCode',
            message: 'Código do jogo (ex: POKER, CHESS):',
            default: projectName.toUpperCase().replace(/-/g, ''),
            validate: (input) => {
                if (!input.trim())
                    return 'Código é obrigatório';
                if (!/^[A-Z0-9]+$/.test(input))
                    return 'Use apenas letras maiúsculas e números';
                return true;
            },
        },
        {
            type: 'input',
            name: 'gameName',
            message: 'Nome do jogo (para exibição):',
            default: projectName.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' '),
        },
        {
            type: 'input',
            name: 'gamesAdminUrl',
            message: 'URL do games-admin:',
            default: 'https://admin.mysys.shop',
        },
        {
            type: 'number',
            name: 'maxPlayers',
            message: 'Número máximo de jogadores:',
            default: 4,
        },
        {
            type: 'confirm',
            name: 'includeBot',
            message: 'Incluir sistema de bot/AI?',
            default: true,
        },
    ]);
    console.log(chalk_1.default.yellow('\nCriando projeto...\n'));
    try {
        await (0, index_1.createGame)({
            projectName,
            gameCode: config.gameCode,
            gameName: config.gameName,
            gamesAdminUrl: config.gamesAdminUrl,
            maxPlayers: config.maxPlayers,
            includeBot: config.includeBot,
        });
        console.log(chalk_1.default.green('\n✓ Projeto criado com sucesso!\n'));
        console.log(chalk_1.default.white('Próximos passos:\n'));
        console.log(chalk_1.default.cyan(`  cd ${projectName}`));
        console.log(chalk_1.default.cyan('  npm install'));
        console.log(chalk_1.default.cyan('  cp .env.example .env'));
        console.log(chalk_1.default.cyan('  # Editar .env com suas configurações'));
        console.log(chalk_1.default.cyan('  npm run dev\n'));
    }
    catch (error) {
        console.error(chalk_1.default.red('\n✗ Erro ao criar projeto:'), error);
        process.exit(1);
    }
}
main();
//# sourceMappingURL=create-game.js.map