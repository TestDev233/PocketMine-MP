<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
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
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissions;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class ReloadCommand extends VanillaCommand {

    public function __construct() {
        parent::__construct(
            "reload",
            "Reloads the server configuration, or a specific configuration file.",
            "/reload [config <ops|bans|ip-bans|server>]"
        );
        $this->setPermission(DefaultPermissions::COMMAND_RELOAD);
    }

    public function execute(CommandSender $sender, string $label, array $args): bool {
        if (!$this->testPermission($sender)) {
            return true;
        }

        $server = Server::getInstance();

        if (count($args) === 0) {
            $sender->sendMessage(TextFormat::YELLOW . "Reloading all configurations...");
            $server->getAsyncConfig()->reload(); // Reload server.properties
            $server->getOps()->reload();
            $server->getNameBans()->reload();
            $server->getIPBans()->reload();
            $sender->sendMessage(TextFormat::GREEN . "All configurations have been reloaded. Note: Plugins cannot be reloaded without a full server restart.");
            return true;
        }

        if (strtolower($args[0]) === "config") {
            if (!isset($args[1])) {
                $sender->sendMessage(TextFormat::RED . "Usage: /reload config <ops|bans|ip-bans|server>");
                return true;
            }

            switch (strtolower($args[1])) {
                case "ops":
                    $server->getOps()->reload();
                    $sender->sendMessage(TextFormat::GREEN . "Reloaded ops.");
                    break;
                case "bans":
                    $server->getNameBans()->reload();
                    $sender->sendMessage(TextFormat::GREEN . "Reloaded name bans.");
                    break;
                case "ip-bans":
                    $server->getIPBans()->reload();
                    $sender->sendMessage(TextFormat::GREEN . "Reloaded IP bans.");
                    break;
                case "server":
                    $server->getAsyncConfig()->reload();
                    $sender->sendMessage(TextFormat::GREEN . "Reloaded server configuration.");
                    break;
                default:
                    $sender->sendMessage(TextFormat::RED . "Unknown config type. Use: ops, bans, ip-bans, server.");
                    break;
            }
            return true;
        }

        $sender->sendMessage(TextFormat::RED . "Unknown subcommand. Use:");
        $sender->sendMessage(TextFormat::YELLOW . "/reload");
        $sender->sendMessage(TextFormat::YELLOW . "/reload config <ops|bans|ip-bans|server>");
        $sender->sendMessage(TextFormat::RED . "Plugin reloading is no longer supported. Please restart the server.");
        return true;
    }
}
