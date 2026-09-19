<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Http\Controllers\Controller;
use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * SRP: the HTTP actions of a config driven admin module.
 *
 * Definition, querying, validation, form data, input preparation and exporting
 * live in their own services, reached through AdminModuleServices; the seam to
 * them is InteractsWithModuleServices, which individual modules override.
 */
abstract class AdminModuleController extends Controller
{
    use InteractsWithModuleServices;

    private const SUBMENU_KEYS = ['type', 'placement', 'section_key', 'row_title'];

    protected string $moduleKey;

    public function __construct(protected readonly AdminModuleServices $modules) {}

    public function index(Request $request): View
    {
        $module = $this->module();
        $pageTitle = $this->pageTitle($module, $request);

        return view($this->viewName('index'), [
            'module' => $module,
            'pageTitle' => $pageTitle,
            'breadcrumbs' => ['Admin', $module['group'] ?? 'Module', $pageTitle],
            'records' => $this->records($request),
            'filters' => $request->only(['search', 'status', 'type']),
            'filterOptions' => $this->modules->formData->filterOptions($module),
        ]);
    }

    public function create(Request $request): View
    {
        $module = $this->module();
        abort_unless(($module['can_create'] ?? true), 403);

        return view($this->viewName('create'), [
            'module' => $module,
            'pageTitle' => $this->modules->definition->actionTitle($module, $request, 'Add'),
            'breadcrumbs' => ['Admin', $module['label'], 'Add'],
            'record' => null,
            'formData' => $this->createFormData($request, $module),
            'options' => $this->formOptions($module),
            'optionAttributes' => $this->formOptionAttributes($module),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $module = $this->module();
        abort_unless(($module['can_create'] ?? true), 403);

        $validated = $this->validateRequest($request, $module);
        $record = $this->persist($this->prepareData($validated, $request, $module), null);

        return redirect()
            ->route($module['route'].'.edit', array_merge([$record->getKey()], $request->only(self::SUBMENU_KEYS)))
            ->with('success', $module['singular'].' created successfully.');
    }

    public function show(int|string $id): View
    {
        $module = $this->module();

        return view($this->viewName('show'), [
            'module' => $module,
            'record' => $this->findRecord($id),
            'pageTitle' => $module['singular'].' Details',
            'breadcrumbs' => ['Admin', $module['label'], 'View'],
        ]);
    }

    public function edit(int|string $id): View
    {
        $module = $this->module();
        abort_unless(($module['can_edit'] ?? true), 403);
        $record = $this->findRecord($id);

        return view($this->viewName('edit'), [
            'module' => $module,
            'pageTitle' => 'Edit '.$module['singular'],
            'breadcrumbs' => ['Admin', $module['label'], 'Edit'],
            'record' => $record,
            'formData' => $this->formData($record, $module),
            'options' => $this->formOptions($module),
            'optionAttributes' => $this->formOptionAttributes($module),
        ]);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $module = $this->module();
        abort_unless(($module['can_edit'] ?? true), 403);

        $record = $this->findRecord($id);
        $validated = $this->validateRequest($request, $module, $record);
        $this->persist($this->prepareData($validated, $request, $module), $record);

        return back()->with('success', $module['singular'].' updated successfully.');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $module = $this->module();
        abort_unless(($module['can_delete'] ?? true), 403);
        $this->findRecord($id)->delete();

        return redirect()
            ->route($module['route'].'.index', request()->only(self::SUBMENU_KEYS))
            ->with('success', $module['singular'].' deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $module = $this->module();
        abort_unless(($module['can_delete'] ?? true), 403);

        $data = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1', 'max:500'],
            'selected_ids.*' => ['integer'],
        ]);

        $records = $this->recordsQuery($module)->whereKey($data['selected_ids'])->get();
        $count = $records->count();
        $records->each->delete();
        $this->bumpCacheVersionForModule();

        return back()->with('success', $count.' '.$module['label'].' deleted successfully.');
    }

    public function export(Request $request, string $format): Response
    {
        abort_unless(in_array($format, ['excel', 'pdf'], true), 404);

        $module = $this->module();
        $records = $this->modules->queries->filtered($request, $module)
            ->orderBy(...($module['sort'] ?? ['id', 'desc']))
            ->limit((int) env('ADMIN_EXPORT_LIMIT', 2000))
            ->get();

        return $this->modules->export->download($format, $this->pageTitle($module, $request), $module, $records);
    }

    public function status(Request $request, int|string $id): RedirectResponse
    {
        $module = $this->module();
        $record = $this->findRecord($id);

        $column = $module['status_column'] ?? (array_key_exists('is_active', $record->getAttributes()) ? 'is_active' : 'status');
        $allowed = $module['status_options'] ?? ['active', 'inactive'];

        $value = $column === 'is_active'
            ? $request->boolean('status')
            : $request->validate(['status' => ['required', Rule::in(array_keys($allowed))]])['status'];

        $record->forceFill([$column => $value])->save();
        $this->bumpCacheVersionForModule();

        return back()->with('success', 'Status updated successfully.');
    }
}
