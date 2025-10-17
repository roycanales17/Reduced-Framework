<?php

    namespace App\Databases\Handler\Blueprints;

    use App\Databases\Handler\Eloquent\Builder;

    class ServerChain extends Builder
    {
        public
        function __construct(string $server) {
            $this->server = $server;
        }

        public function query(string $query, array $params = []): Builder {
            $this->query = $query;

            $bindings = [];
            foreach ($params as $key => $value) {
                $bindings[(str_starts_with($key, ':') || is_int($key) ? $key : ":$key")] = $value;
            }

            $this->bindings = $bindings;
            return $this;
        }
    }
