<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\muqsit\simplepackethandler\interceptor;

use Closure;
use pocketmine\plugin\Plugin;

final class PacketInterceptor implements IPacketInterceptor{

	readonly private PacketInterceptorListener $listener;

	public function __construct(Plugin $register, int $priority, bool $handle_cancelled){
		$this->listener = new PacketInterceptorListener($register, $priority, $handle_cancelled);
	}

	public function interceptIncoming(Closure $handler) : IPacketInterceptor{
		$this->listener->interceptIncoming($handler);
		return $this;
	}

	public function interceptOutgoing(Closure $handler) : IPacketInterceptor{
		$this->listener->interceptOutgoing($handler);
		return $this;
	}

	public function unregisterIncomingInterceptor(Closure $handler) : IPacketInterceptor{
		$this->listener->unregisterIncomingInterceptor($handler);
		return $this;
	}

	public function unregisterOutgoingInterceptor(Closure $handler) : IPacketInterceptor{
		$this->listener->unregisterOutgoingInterceptor($handler);
		return $this;
	}
}