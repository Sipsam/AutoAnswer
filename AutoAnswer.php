<?php
/**
 * @name AutoAnswer
 * @main sipsam\AutoAnswer
 * @author sipsam1
 * @version 1.0.1
 * @api 4.0.0
 */
namespace sipsam{
	class AutoAnswer extends \pocketmine\plugin\PluginBase implements \pocketmine\event\Listener{
		public function onEnable(){
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
			@mkdir($this->getDataFolder());
			$this->config = (new \pocketmine\utils\Config($this->getDataFolder()."answer.yml", \pocketmine\utils\Config::YAML, [
					"안녕" => "안녕하세요"
			]));
			$this->file = $this->config->getAll();
			$this->mod = [];
			$command = new \pocketmine\command\PluginCommand("자동응답기", $this);
			$command->setPermission("OP");
			$command->setDescription("자동응답 문구 설정");
			$this->getServer()->getCommandMap()->register("자동응답기", $command);
		}
		public function onCommand(\pocketmine\command\CommandSender $sender, \pocketmine\command\Command $command, string $label, array $args): bool{
			if(strtolower($command) == "자동응답기"){
				if($sender instanceof \pocketmine\Player){
					if(!$sender->isOp()) return true;
					if(!isset($args[0])){
						$sender->sendMessage("/자동응답기 <추가/삭제>");
						return true;
					}
					if($args[0] == "추가"){
						$sender->sendMessage("추가할 키워드를 입력해주세요");
						$this->mod[$sender->getName()]["mod"] = "add1";
						return true;
					}elseif($args[0] == "삭제"){
						$sender->sendMessage("제거할 키워드를 입력해주세요");
						$this->mod[$sender->getName()]["mod"] = "remove";
						return true;
					}
					$sender->sendMessage("/자동응답기 <추가/삭제>");
					return true;
				}
				return true;
			}
			return false;
		}
		public function onChat(\pocketmine\event\player\PlayerChatEvent $ev){
			$player = $ev->getPlayer();
			$name = $player->getName();
			$message = $ev->getMessage();
			if(isset($this->mod[$name])){
				if($this->mod[$name]["mod"] == "add1"){
					$this->mod[$name]["mod"] = "add2";
					$this->mod[$name]['keyword'] = $message;
					$player->sendMessage("해당 키워드의 응답 문구를 입력해주세요");
					$ev->setCancelled();
				}elseif($this->mod[$name]["mod"] == "add2"){
					$this->file[$this->mod[$name]['keyword']] = $message;
					$player->sendMessage("자동 응답 문구가 추가되었습니다");
					$this->config->setAll($this->file);
					$this->config->save();
					unset($this->mod[$name]);
					$ev->setCancelled();
				}elseif($this->mod[$name]["mod"] == "remove"){
					if(isset($this->file[$message])){
						unset($this->mod[$name]);
						unset($this->file[$message]);
						$this->config->setAll($this->file);
						$this->config->save();
						$player->sendMessage("해당 키워드가 삭제되었습니다");
						$ev->setCancelled();
						return;
					}
					$player->sendMessage("그런 키워드는 존재하지 않습니다");
					$player->sendMessage("다시 입력해주세요");
					$ev->setCancelled();
				}
			}else{
				if(isset($this->file[$message])){
					if(isset(explode("/", $this->file[$message])[1])){
						$array = explode("/", $this->file[$message]);
						$msg = implode("\n", $array);
						$player->sendMessage($msg);
						$ev->setCancelled();
						return;
					}
					$player->sendMessage($this->file[$message]);
					$ev->setCancelled();
				}
			}
		}
		public function onDisable(){
			unset($this->mod);
		}
	}
}
