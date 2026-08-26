<style>
    .apexcharts-tooltip,
    .apexcharts-tooltip-title {
        background: #ffffff !important;
        border-color: #d8e1ec !important;
        color: #111827 !important;
        box-shadow: 0 14px 38px rgba(16, 42, 67, .18) !important;
    }

    .apexcharts-tooltip *,
    .apexcharts-tooltip-title * {
        color: #111827 !important;
    }

    .apexcharts-tooltip-series-group {
        background: #ffffff !important;
    }
</style>
<script>
    window.MySignalCharts = window.MySignalCharts || (() => {
        const numberFormatter = new Intl.NumberFormat('fr-FR');

        const toNumber = (value) => Number(value || 0);
        const totalOf = (series) => series.reduce((total, value) => total + toNumber(value), 0);
        const percentOf = (value, total) => total > 0 ? ((toNumber(value) / total) * 100).toFixed(1).replace('.', ',') : '0,0';
        const formatNumber = (value) => numberFormatter.format(toNumber(value));
        const labelWithPercent = (value, total) => `${formatNumber(value)} (${percentOf(value, total)} %)`;
        const visibleLabelWithPercent = (value, total) => toNumber(value) > 0 ? labelWithPercent(value, total) : '';

        const valueFromOptions = (options, fallbackValue) => {
            const series = options?.w?.config?.series || [];
            const seriesIndex = options?.seriesIndex ?? 0;
            const dataPointIndex = options?.dataPointIndex;

            if (Array.isArray(series[seriesIndex]?.data) && dataPointIndex !== undefined) {
                return series[seriesIndex].data[dataPointIndex];
            }

            return Array.isArray(series) ? (series[seriesIndex] ?? fallbackValue) : fallbackValue;
        };

        const totalFromOptions = (options) => {
            const globals = options?.w?.globals;

            if (Array.isArray(globals?.seriesTotals)) {
                return totalOf(globals.seriesTotals);
            }

            const configSeries = options?.w?.config?.series || [];

            if (Array.isArray(configSeries[0]?.data)) {
                return totalOf(configSeries.flatMap((serie) => serie.data || []));
            }

            return totalOf(configSeries);
        };

        const tooltipWithTotal = (total) => ({
            theme: 'light',
            y: {
                formatter: (value) => labelWithPercent(value, total),
            },
        });

        return {
            donutDataLabels: (series = null) => ({
                enabled: true,
                formatter: (_percent, options) => {
                    const total = Array.isArray(series) ? totalOf(series) : totalFromOptions(options);
                    const value = valueFromOptions(options, 0);

                    return visibleLabelWithPercent(value, total);
                },
                style: {
                    fontSize: '12px',
                    fontWeight: 600,
                    colors: ['#ffffff'],
                },
                dropShadow: {
                    enabled: true,
                    top: 1,
                    left: 1,
                    blur: 2,
                    opacity: 0.45,
                },
            }),
            barDataLabels: (series = null) => ({
                enabled: true,
                formatter: (value, options) => {
                    const total = Array.isArray(series) ? totalOf(series) : totalFromOptions(options);

                    return visibleLabelWithPercent(value, total);
                },
                offsetY: 8,
                style: {
                    fontSize: '11px',
                    fontWeight: 600,
                    colors: ['#ffffff'],
                },
                background: {
                    enabled: false,
                },
                dropShadow: {
                    enabled: true,
                    top: 1,
                    left: 1,
                    blur: 2,
                    opacity: 0.5,
                },
            }),
            areaDataLabels: (series = null) => ({
                enabled: true,
                formatter: (value, options) => {
                    const total = Array.isArray(series) ? totalOf(series) : totalFromOptions(options);

                    return visibleLabelWithPercent(value, total);
                },
                offsetY: -8,
                style: {
                    fontSize: '10px',
                    fontWeight: 600,
                    colors: ['#1f2933'],
                },
                background: {
                    enabled: true,
                    foreColor: '#1f2933',
                    color: '#ffffff',
                    borderRadius: 6,
                    padding: 3,
                    opacity: 0.9,
                    borderWidth: 1,
                    borderColor: '#e5edf5',
                },
            }),
            tooltip: (series = null) => {
                if (Array.isArray(series)) {
                    return tooltipWithTotal(totalOf(series));
                }

                return {
                    theme: 'light',
                    y: {
                        formatter: (value, options) => labelWithPercent(value, totalFromOptions(options)),
                    },
                };
            },
        };
    })();
</script>
