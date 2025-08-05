<?php

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginManager;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use function array_slice;
use function count;
use function implode;
use function strtolower;

class ReloadCommand extends VanillaCommand{

    public function __construct(){
        parent::__construct(
            "reload",
            KnownTranslationFactory::pocketmine_command_reload_description(),
            "/reload [config <ops|bans|ip-bans|server>|plugin <name>]"
        );
        $this->setPermission(DefaultPermissionNames::COMMAND_RELOAD);
    }

    public function execute(CommandSender $sender, string $label, array $args) : bool{
        if(!$this->testPermission($sender)){
            return true;
        }

        $server = Server::getInstance();
        $pluginManager = $server->getPluginManager();

        if(count($args) === 0){
            $sender->sendMessage(TextFormat::YELLOW . "Reloading all configurations and plugins...");

            $server->reloadConfig();
            $server->reloadWhitelist();
            $server->reloadOps();
            $server->getNameBans()->load();
            $server->getIPBans()->load();

            $pluginManager->disablePlugins();
            $pluginManager->enablePlugins(PluginManager::STARTUP);

            foreach($pluginManager->getPlugins() as $plugin){
                if($plugin instanceof PluginBase){
                    $plugin->onReload();
                }
            }

            $sender->sendMessage(TextFormat::GREEN . "All configurations and plugins have been reloaded.");
            return true;
        }

        switch(strtolower($args[0])){
            case "config":
                if(!isset($args[1])){
                    $sender->sendMessage(TextFormat::RED . "Usage: /reload config <ops|bans|ip-bans|server>");
                    return true;
                }

                switch(strtolower($args[1])){
                    case "ops":
                        $server->reloadOps();
                        $sender->sendMessage(TextFormat::GREEN . "Reloaded ops.");
                        break;
                    case "bans":
                        $server->getNameBans()->load();
                        $sender->sendMessage(TextFormat::GREEN . "Reloaded name bans.");
                        break;
                    case "ip-bans":
                        $server->getIPBans()->load();
                        $sender->sendMessage(TextFormat::GREEN . "Reloaded IP bans.");
                        break;
                    case "server":
                        $server->reloadConfig();
                        $sender->sendMessage(TextFormat::GREEN . "Reloaded server configuration.");
                        break;
                    default:
                        $sender->sendMessage(TextFormat::RED . "Unknown config type. Use: ops, bans, ip-bans, server.");
                        break;
                }
                return true;

            case "plugin":
                if(!isset($args[1])){
                    $sender->sendMessage(TextFormat::RED . "Usage: /reload plugin <pluginName>");
                    return true;
                }

                $pluginName = implode(" ", array_slice($args, 1));
                $plugin = $pluginManager->getPlugin($pluginName);

                if($plugin instanceof Plugin){
                    $pluginManager->disablePlugin($plugin);
                    $pluginManager->enablePlugin($plugin);

                    if($plugin instanceof PluginBase){
                        $plugin->onReload();
                    }

                    $sender->sendMessage(TextFormat::GREEN . "Reloaded plugin: " . TextFormat::YELLOW . $plugin->getName());
                }else{
                    $sender->sendMessage(TextFormat::RED . "Plugin \"$pluginName\" not found.");
                }
                return true;

            default:
                $sender->sendMessage(TextFormat::RED . "Unknown subcommand. Use:");
                $sender->sendMessage("/reload");
                $sender->sendMessage("/reload config <ops|bans|ip-bans|server>");
                $sender->sendMessage("/reload plugin <pluginName>");
                return true;
        }
    }
}
