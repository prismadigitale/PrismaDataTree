# Implementation Plan: PrismaDataTree (TreeLine Replica)

This document outlines the step-by-step implementation plan to replicate the core concepts of "TreeLine" using Laravel 12, Filament v5, and Livewire 4.

## Project Goal
Create a hierarchical information manager where users can define custom Data Types (schemas) and organize content in a Tree structure. Each Node in the tree has a specific Type, which determines the input form fields available.

## Core Architecture

### 1. Database Schema (The Meta-Model)
We will use a meta-model approach to allow dynamic field definitions.

*   **`fields` Table**: Defines the available attributes.
    *   `id`, `name` (internal slug), `label`, `type` (text, number, date, select, image, etc.), `options` (JSON: validation rules, default values, choices for selects).
*   **`data_types` Table**: Defines the "Templates" for nodes.
    *   `id`, `name`, `icon` (string), `default_child_type_id` (optional).
*   **`data_type_field` Pivot Table**: Associates Fields to Types.
    *   `data_type_id`, `field_id`, `sort_order`.
*   **`nodes` Table**: The actual content.
    *   `id`, `parent_id` (nullable), `data_type_id`, `title`, `position` (for ordering), `data` (JSON column storing dynamic values).

### 2. User Interface Structure
*   **Global Layout**:
    *   **Horizontal Top Menu**: Main application actions (Settings, Profile, etc.).
    *   **Sidebar**: Interactive Tree View of the `nodes`.
    *   **Main Content Area**: The form/view for the currently selected node.

## detailed Implementation Steps

### Phase 1: Foundation & Migrations
1.  **Create Models & Migrations**:
    *   `Field`: Represents a single input definition.
    *   `DataType`: Represents a collection of fields (a schema).
    *   `Node`: The main entity. Uses `NestedSet` (via `kalnoy/nestedset`) or standard parent-child adjacency list compatible with the chosen Tree View plugin. *Recommendation: Use Adjacency List with `order` column as required by most Filament tree plugins.*
2.  **Define Relationships**:
    *   `DataType` belongsToMany `Field`.
    *   `Node` belongsTo `DataType`.
    *   `Node` parent-child standard relations.

### Phase 2: The Meta-Layer (Structure Management)
*Construction of the tools that allow users to define their data structures.*

3.  **Filament Resource: `FieldResource`**
    *   CRUD to create new fields.
    *   Form: Name, Label, Input Type (Select: Text, Number, Textarea, RichEditor, Date, Toggle, Select), Validation Rules (Regex, Required), Options (for Select types).
4.  **Filament Resource: `DataTypeResource`**
    *   CRUD to create Data Types (e.g., "Contact", "Recipe", "Project").
    *   Form: Name, Icon Picker.
    *   **Repeater/Relation Manager**: Attach `Fields` to this Type and reorder them.

### Phase 3: The Tree Interface
*Integration of the navigation structure.*

5.  **Install Tree Plugin**: `openplain/filament-tree-view` (or compatible alternative if issues arise).
6.  **Node Tree Page**:
    *   Create a Custom Page or Resource using the Tree View.
    *   Sidebar integration: The tree should likely be the primary navigation method, optionally placed in a sidebar widget or a full-height layout split.

### Phase 4: Dynamic Node Management (The Core)
*The complex logic to render forms based on data types.*

7.  **Dynamic Form Generating Service**:
    *   Create a Service that takes a `DataType` and returns a `Schema` array for Filament Forms.
    *   It iterates through the Type's `Fields` and instantiates the corresponding Filament Form Component (e.g., `TextInput::make($field->name)`).
8.  **Node Editing Interface**:
    *   When a Node is clicked in the Tree, load its Edit Form.
    *   **Form Structure**:
        *   **Static Fields**: Title, Parent (hidden/readonly), DataType Selector (allows changing type).
        *   **Dynamic Section**: Renders the fields from Step 7 based on the selected `DataType` (stored in the `data` JSON column).
    *   **Reactivity**: If the user changes the `DataType` dropdown, the dynamic section must refresh (using Livewire `$refresh`) to show the new fields.
9.  **Node Creation Logic**:
    *   When creating a child, pre-select the `DataType` based on the Parent's `default_child_type_id` or the Parent's own type.

### Phase 5: Refinement & Advanced Features
10. **Icons**: Display the `DataType` icon in the Tree View for each node.
11. **Inheritance Logic**: Ensure children inherit defaults correctly.
12. **Type Switching Safety**: (Bonus) Warning/Mapping when switching types (e.g., "Field 'Phone' exists in both types, value preserved" vs "Field 'Salary' lost").

## Technical Considerations
*   **JSON Handling**: The `nodes` table will use a JSON column `data`. In the Filament Form, we will use `->statePath('data')` to group dynamic fields or map them manually.
*   **Performance**: Eager load `dataType.fields` when rendering nodes to avoid N+1 issues.

## User Review Required
*   Confirm choice of **JSON column** for dynamic data storage (most flexible, standard for this use case with Filament).
*   Confirm using `openplain/filament-tree-view` as the implementation base.
