<?php

namespace App\Services\Admin\Access;

use App\Contracts\Admin\Access\AdminRouteRequirementContract;
use App\Contracts\Admin\Access\SalesChannelLookupContract;
use App\Data\Admin\Access\AccessRequirement;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

/**
 * Maps an admin route to the section + action it needs, using
 * config/admin_access.php. Sales routes resolve the Customer / Dealer
 * section from the record itself, so editing ?type= in the URL cannot open
 * the other channel's records.
 */
final class AdminRouteRequirements implements AdminRouteRequirementContract
{
    private const NON_ID_PARAMETERS = ['format', 'document', 'report', 'module'];

    public function __construct(private readonly SalesChannelLookupContract $channels) {}

    public function forRequest(Request $request): AccessRequirement
    {
        $route = $request->route();
        $name = $route instanceof Route ? (string) $route->getName() : '';
        if ($name === '') {
            return AccessRequirement::superOnly();
        }

        $explicit = (array) config('admin_access.routes', []);
        if (array_key_exists($name, $explicit)) {
            return $explicit[$name] === null ? AccessRequirement::open() : $this->explicit($explicit[$name], $request, $route);
        }

        $parts = explode('.', $name);
        $action = config('admin_access.route_actions.'.end($parts));
        if (count($parts) < 3 || ! is_string($action)) {
            return AccessRequirement::superOnly();
        }

        $module = $parts[1];
        if ($module === 'sales-documents') {
            $module = config('admin_access.sales_documents.'.$route->parameter('document'));
            if (! is_string($module)) {
                return AccessRequirement::superOnly();
            }
        }

        return $this->forModule($module, $action, $request, $route);
    }

    public function sectionsForModule(string $module, ?string $channel): array
    {
        $module = (string) (config('admin_access.module_sections.'.$module) ?? $module);
        if (! $this->isChannelModule($module)) {
            return [$module];
        }

        $channels = in_array($channel, $this->allChannels(), true) ? [$channel] : $this->allChannels();

        return array_map(fn (string $c): string => $c.'-'.$module, $channels);
    }

    /**
     * @param  array{0: string, 1: string}  $rule
     */
    private function explicit(array $rule, Request $request, Route $route): AccessRequirement
    {
        [$section, $action] = $rule;

        if ($section === '{module}') {
            return $this->forModule((string) $route->parameter('module'), $action, $request, $route);
        }

        $section = preg_replace_callback('/\{(\w+)\}/', fn (array $m): string => (string) $route->parameter($m[1]), $section);

        return AccessRequirement::needs([[(string) $section, $action]]);
    }

    private function forModule(string $module, string $action, Request $request, Route $route): AccessRequirement
    {
        $module = (string) (config('admin_access.module_sections.'.$module) ?? $module);
        $channels = $this->isChannelModule($module) ? $this->channelsFor($module, $request, $route) : [null];

        return AccessRequirement::needs(array_map(
            fn (?string $channel): array => [$channel ? $channel.'-'.$module : $module, $action],
            $channels,
        ));
    }

    /**
     * Channels touched by this request: the record's own, any channel the
     * submitted data moves it to, selected rows for a bulk delete, else the
     * ?type= of the page. Anything unknown needs both channels.
     *
     * @return list<string>
     */
    private function channelsFor(string $module, Request $request, Route $route): array
    {
        $found = [];
        $unknown = false;

        $ids = array_values(array_filter(
            $route->parameters(),
            fn ($value, $key): bool => ! in_array($key, self::NON_ID_PARAMETERS, true) && is_scalar($value),
            ARRAY_FILTER_USE_BOTH,
        ));
        $selected = array_values(array_filter((array) $request->input('selected_ids', []), 'is_scalar'));
        $lookupIds = [...$ids, ...(str_ends_with((string) $route->getName(), '.bulk-destroy') ? $selected : [])];

        if ($lookupIds !== []) {
            $channels = $this->channels->forRecords($module, $lookupIds);
            foreach ($lookupIds as $id) {
                $channel = $channels[$id] ?? null;
                if ($channel) {
                    $found[] = $channel;
                } else {
                    $unknown = true;
                }
            }
        }

        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            if ($module === 'orders' && $request->filled('order_type')) {
                $found[] = (string) $request->input('order_type');
            } elseif ($module !== 'orders' && $request->filled('order_id')) {
                $channel = $this->channels->forOrder((string) $request->input('order_id'));
                if ($channel) {
                    $found[] = $channel;
                } else {
                    $unknown = true;
                }
            }
        }

        if ($found === [] && ! $unknown && is_string($request->query('type', $request->input('type')))) {
            $found[] = (string) $request->query('type', $request->input('type'));
        }

        $valid = array_values(array_unique(array_intersect($found, $this->allChannels())));

        return $unknown || $valid === [] || count($valid) < count(array_unique($found)) ? $this->allChannels() : $valid;
    }

    private function isChannelModule(string $module): bool
    {
        return in_array($module, (array) config('admin_access.channel_modules', []), true);
    }

    /**
     * @return list<string>
     */
    private function allChannels(): array
    {
        return array_values((array) config('admin_access.channels', []));
    }
}
