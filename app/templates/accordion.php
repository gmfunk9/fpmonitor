<div class="website-wrapper">
    <?php $latestEntry = array_shift($tableData);?>
    <?php $tableNameHR = str_replace('_', '.', $tableName); ?>

    <div class="accordion-title fp_flex fp_flex-row">
        <button class='accordion-button'><?= $tableNameHR ?></button>
        <div class="latest-entry">
            <span class="timestamp"><?= date('j F Y - H:i:s', strtotime($latestEntry['timestamp'])) ?></span>
            <span class="separator">-</span>
            <span class="response-code" data-rcode="<?= $latestEntry['response_code'] ?>"><?= $latestEntry['response_code'] ?></span>
        </div>

    </div>

    <div class='panel'>
        <div class="selectors">
            <select id="<?= $tableName ?>-date-selector" class="date-selector">
                <option value="1">Last 1 day</option>
                <option value="7">Last 7 days</option>
                <option value="14">Last 14 days</option>
                <option value="30">Last 30 days</option>
                <option value="365">Last 1 Year</option>
                <option value="1095">Last 3 Year</option>
                <option value="1826">Last 5 Year</option>
                <option value="3652">Last 10 Year</option>
            </select>
            <select id="<?= $tableName ?>-granularity-selector" class="granularity-selector">
                <option value="hour">1 hour</option>
                <option value="4hours">4 hours</option>
                <option value="8hours">8 hours</option>
                <option value="day">1 day</option>
                <option value="2days">2 days</option>
                <option value="3days">3 days</option>
                <option value="week">1 week</option>
                <option value="2weeks">2 weeks</option>
                <option value="month">1 month</option>
                <option value="3months">3 months</option>
                <option value="6months">6 months</option>
                <option value="9months">9 months</option>
                <option value="year">1 year</option>
                <option value="3years">3 years</option>
                <option value="5years">5 years</option>
            </select>

        </div>
        <div class='table-container'>

            <table>
                <thead>
                    <tr>
                        <?php foreach ($latestEntry as $column => $value): ?>
                            <th><?= $column ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($tableData, 0, 5) as $row): ?>
                        <tr>
                            <?php foreach ($row as $value): ?>
                                <td><?= $value ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div id="<?= $tableName ?>-chart-container" class="chart-container" style="display: none;">
            <canvas id="<?= $tableName ?>-chart"></canvas>
        </div>
        <button class="load-more" data-table="<?= $tableName ?>" data-offset="5">Load More</button>
        <button class="toggle-chart" data-table="<?= $tableName ?>">Toggle Chart</button>
    </div>
</div>