<?php

/*
 *
 * ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use function count;
use function strtolower;

class ReloadCommand extends VanillaCommand{

	public function execute(CommandSender $sender, string $label, array $args) : bool{
		if(!$sender->hasPermission("pocketmine.command.reload")){
			$sender->sendMessage(TextFormat::RED . "You do not have permission to use this command.");
			return true;
		}

		$server = Server::getInstance();

		if(count($args) === 0 || strtolower($args[0] ?? "") === "all"){
			$sender->sendMessage(TextFormat::YELLOW . "Restarting all plugins...");

			$pluginManager = $server->getPluginManager();
			$plugins = $pluginManager->getPlugins();
			foreach($plugins as $plugin){
				if($plugin->isEnabled()){
					$pluginManager->disablePlugin($plugin);
					$pluginManager->enablePlugin($plugin);
				}
			}

			$sender->sendMessage(TextFormat::GREEN . "All plugins have been restarted.");
			return true;
		}

		if(strtolower($args[0] ?? "") === "plugin"){
			if(!isset($args[1])){
				$sender->sendMessage(TextFormat::RED . "Usage: /reload plugin <plugin_name>");
				return true;
			}

			$pluginName = $args[1];
			$plugin = $server->getPluginManager()->getPlugin($pluginName);

			if($plugin === null){
				$sender->sendMessage(TextFormat::RED . "Plugin '" . $pluginName . "' not found.");
				return true;
			}

			$sender->sendMessage(TextFormat::YELLOW . "Restarting plugin '" . $pluginName . "'...");

			$pluginManager = $server->getPluginManager();
			$pluginManager->disablePlugin($plugin);
			$pluginManager->enablePlugin($plugin);

			$sender->sendMessage(TextFormat::GREEN . "Plugin '" . $pluginName . "' has been restarted.");
			return true;
		}
		
		$sender->sendMessage(TextFormat::RED . "Unknown subcommand. Use:");
		$sender->sendMessage(TextFormat::YELLOW . "/reload [all]");
		$sender->sendMessage(TextFormat::YELLOW . "/reload plugin <plugin_name>");
		return true;
	}
}
