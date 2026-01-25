## Mary UI Livewire Components - Guide

### Overview
Mary UI is a collection of gorgeous Laravel Blade UI components designed for Livewire 4, styled with daisyUI v5 and Tailwind CSS v4. It provides pre-built, responsive components that integrate seamlessly with Livewire, allowing you to build dynamic interfaces without writing JavaScript.

### Component prefix tag
The Mary UI components for this project use the prefix `x-mary-`:

@verbatim
<code-snippet name="Component using prefix" lang="blade">
    <x-mary-select
        label="City"
        wire:model="city_id"
        icon="o-flag"
        :options="$cities"
    />
</code-snippet>
@endverbatim

For all components, you must use the prefix `x-mary-` to avoid conflicts with other components.

### Best Practices
- Use Icons: Mary UI supports Blade Icons (https://blade-ui-kit.com) for Heroicons with prefixes like `o-` (outline) and `s-` (solid), and FontAwesome with prefixes like `fas.` (solid), `far.` (regular), or `fab.` (brands).

@verbatim
<code-snippet name="Heroicons icon component" lang="blade">
    <x-mary-icon name="o-envelope" />
    <x-mary-icon name="s-envelope" />
</code-snippet>
@endverbatim

@verbatim
<code-snippet name="FontAwesome icon component" lang="blade">
    <x-mary-icon name="fas.cloud" />
    <x-mary-icon name="far.circle-play" />
    <x-mary-icon name="fab.facebook" />
</code-snippet>
@endverbatim

- Responsive Design: Use Tailwind's responsive classes (e.g., hidden lg:table-cell)
- Wire Model: Always use wire:model for reactive data binding
- Slots: Leverage slots for actions, headers, and custom content
- Spinners: Add spinner attribute to buttons for loading states

### External Dependencies (Cropper.js, etc.)

Some Mary UI components require external libraries. Use Livewire's `@assets` directive to load them only when needed:

@verbatim
<code-snippet name="Loading Cropper.js for file component" lang="blade">
@assets
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
@endassets

<x-mary-file wire:model="avatar" accept="image/png, image/jpeg" crop-after-change>
    <img src="{{ $avatar }}" class="h-24 rounded-lg"/>
</x-mary-file>
</code-snippet>
@endverbatim

Components requiring Cropper.js:
- `x-mary-file` with `crop-after-change` attribute

### Resources
- Documentation: https://mary-ui.com/
- GitHub: https://github.com/robsontenorio/mary
- Blade UI kit Icons: https://blade-ui-kit.com/blade-icons

### Quick Tips
- Search for components using Command + G (or Ctrl + G) on the documentation site
- All components are responsive by default
- You can override styles inline using daisyUI v5 and Tailwind v4 classes
- The package follows DRY principles - write less, achieve more

### Daisy UI
- Use the daisyUI library for styling components if you need more customization.
- Use daisyUI MCP server to generate components that Mary UI haven't implemented yet.
