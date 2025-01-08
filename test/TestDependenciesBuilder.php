<?php

declare(strict_types=1);

namespace Tests\GenericLibrary\AirTable;

use App\GenericLibrary\AirTable\AirTable;
use App\GenericLibrary\AirTable\DBClient;
// PHPDocでは使用しているので警告を無効にする
// phpcs:ignore-next-line
use App\GenericLibrary\AirTable\Query;

class TestDependenciesBuilder
{
    /**
     * Builds test dependencies.
     *
     * @return array{
     *     apiMockConnector: APIConnectorDummy,
     *     realClient: DBClient,
     *     mockClient: DBClient,
     *     query: Query
     * }
     */

    public static function build(): array
    {
        $dependencies = [];

        $records                          = [Faker::wrestlerNameRecord()];
        $dependencies['apiMockConnector'] = new APIConnectorDummy($records);
        $dependencies['mockClient']       = AirTable::buildClient($dependencies['apiMockConnector']);
        $dependencies['query']            = AirTable::buildInitializedQuery();

        // QueryをPHPDocで使用しているので無理やり使用してQueryのUseをLintの自動修正を実行したときに消えないようにしている
        //        @phpstan-ignore-next-line
        assert($dependencies['query'] instanceof Query);

        $dependencies['realClient']       = app(DBClient::class);

        return $dependencies;
    }
}
