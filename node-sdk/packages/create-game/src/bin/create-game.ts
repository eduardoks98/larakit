#!/usr/bin/env node

// ==========================================
// CREATE GAME CLI
// CLI para criar novos jogos MySys
// ==========================================

import { createGame } from '../index';
import chalk from 'chalk';
import inquirer from 'inquirer';

async function main() {
  console.log(chalk.cyan('\n  MySys Game SDK - Create Game\n'));

  // Pegar nome do projeto dos argumentos ou perguntar
  let projectName = process.argv[2];

  if (!projectName) {
    const answers = await inquirer.prompt([
      {
        type: 'input',
        name: 'projectName',
        message: 'Nome do projeto:',
        default: 'my-game',
        validate: (input: string) => {
          if (!input.trim()) return 'Nome é obrigatório';
          if (!/^[a-z0-9-]+$/.test(input)) return 'Use apenas letras minúsculas, números e hífens';
          return true;
        },
      },
    ]);
    projectName = answers.projectName;
  }

  // Perguntar configurações
  const config = await inquirer.prompt([
    {
      type: 'input',
      name: 'gameCode',
      message: 'Código do jogo (ex: POKER, CHESS):',
      default: projectName.toUpperCase().replace(/-/g, ''),
      validate: (input: string) => {
        if (!input.trim()) return 'Código é obrigatório';
        if (!/^[A-Z0-9]+$/.test(input)) return 'Use apenas letras maiúsculas e números';
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

  console.log(chalk.yellow('\nCriando projeto...\n'));

  try {
    await createGame({
      projectName,
      gameCode: config.gameCode,
      gameName: config.gameName,
      gamesAdminUrl: config.gamesAdminUrl,
      maxPlayers: config.maxPlayers,
      includeBot: config.includeBot,
    });

    console.log(chalk.green('\n✓ Projeto criado com sucesso!\n'));
    console.log(chalk.white('Próximos passos:\n'));
    console.log(chalk.cyan(`  cd ${projectName}`));
    console.log(chalk.cyan('  npm install'));
    console.log(chalk.cyan('  cp .env.example .env'));
    console.log(chalk.cyan('  # Editar .env com suas configurações'));
    console.log(chalk.cyan('  npm run dev\n'));
  } catch (error) {
    console.error(chalk.red('\n✗ Erro ao criar projeto:'), error);
    process.exit(1);
  }
}

main();
