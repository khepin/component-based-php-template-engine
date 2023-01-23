<?php declare(strict_types=1);

/**
 * $stack holds the stack of components being rendered. Components get added on their open tag and get removed
 * once their closing tag is reached.
 * <?= component('name') ?> until <?= end_component('name') ?>
 * Mis-stacked components will throw an exception. If `component('header')` was the last open component, but the
 * next closing tag is `end_component('body')` for example.
 */
$stack = [];

/**
 * $slotstack holds the stack of slots within a given component.
 * This allows to use multiple named slots within a component.
 */
$slotstack = [];


/**
 * Starts rendering a component. No rendering is done yet as we might need to first capture the slots within the
 * component to properly render the component itself.
 *
 * @param string $_____name the name of (path to) the component
 * @param mixed ...$args any properties to be passed to the component. Those must be named arguments. Similar to how you
 * would pass properties on an HTML element.
 * @return void
 */
function component(string $_____name, mixed ...$args) {
    // The stacks are global to the rendering engine and need to be imported
    global $stack;
    global $slotstack;

    // An empty slotstack is created for the slots within this component.
    $slotstack[] = [];
    // The current component's name and passed in properties are added to the stack
    $stack[] = [$_____name, $args];
    // Open an output buffer and return.
    // Either there are slots to be rendered within the component and we now render those, or we immediately reach
    // the end component tag.
    ob_start();
    return '';
}

/**
 * Does the actual rendering of a component based on its passed in properties and the captured slots.
 *
 * @param string $_____name
 * @return void
 */
function end_component(string $_____name) {
    // Get the global stacks
    global $stack;
    global $slotstack;
    // Get the last elements on the stack for each stack
    $slots = array_pop($slotstack);
    $call = array_pop($stack);
    // If the component being closed isn't the last in the stack, the template is improperly ordered and we throw
    // an exception.
    $cname = $call[0];
    if ($_____name !== $cname) {
        throw new \Exception("Trying to close $_____name but $cname is currently in stack");
    }

    // Capture the content of a non-named slot in $slot
    $slot = ob_get_clean();
    // The properties that need to be given to render the component are grabbed from the last element on the stack
    // that we popped earlier
    $args = $call[1];
    return render_component($cname, $args, $slot, $slots);
}

/**
 * Not for end user usage. This method does the actual rendering of a component. Passing the components all its
 * arguments, properties and slots as variables.
 *
 * @param string $_____name
 * @param array $args
 * @param string $slot
 * @param array $slots
 * @return void
 */
function render_component(string $_____name, array $args, string $slot, array $slots) {
    // $args and $slots are maps of properties and of named slots. `extract` takes each of those key value pairs and makes
    // the value available in a variable named after the key.
    extract($args); extract($slots);
    ob_start();
    // When including a file, it can access all the variables that exist within the current scope, so that's all the
    // function arguments, but also all the variables that were `extract`ed.
    include __DIR__.'/'.$_____name.'.php';
    return ob_get_clean();
}

/**
 * Renders a component that doesn't use slots as a single tag `<?= simple_component('time_since', time()) ?>`
 *
 * @param string $_____name
 * @param mixed ...$args
 * @return void
 */
function simple_component(string $_____name, mixed ...$args) {
    component($_____name, ...$args);
    return end_component($_____name);
}

/**
 * Marks the beginning of a named slot
 *
 * `<?= slot('header') ?>`
 * `<?= end_slot('header') ?>`
 *
 * @param string $_____name
 * @return void
 */
function slot(string $_____name) {
    global $slotstack;
    // Mark the slot's name in the slot stack
    $slotstack[count($slotstack) - 1][] = [$_____name];
    ob_start();
    return '';
}

/**
 * Marks the end of a slot, captures its rendered content and adds it as a named slot in the last element of the
 * slot stack.
 *
 * @param string $_____name
 * @return void
 */
function end_slot(string $_____name) {
    // get the global slotstack
    global $slotstack;
    // The slot we're trying to end should be the last in the stack
    // Note that slotstack is a stack of stacks. 1 stack item for each component, and then 1 stack item for each
    // slot in this component
    $slot = array_pop($slotstack[count($slotstack) - 1]);
    $sname = $slot[0];
    if ($_____name !== $sname) {
        throw new \Exception("trying to end slot $_____name but $sname is current slot in stack");
    }

    $content = ob_get_clean();
    // We re-use the same component level stack as both an array and a map. String keys are for named slots that have
    // already been rendered.
    $slotstack[count($slotstack) - 1][$_____name] = $content;
    return '';
}
