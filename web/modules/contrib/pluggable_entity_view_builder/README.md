> Pluggable Entity View Builder (PEVB)

Define entity view builders as plugins, defined per bundle.

## Quickstart

This module comes with an example module, so you could quickly give it a spin.

1. Enable "Pluggable Entity View Builder Example" module.
1. Clear cache, so the new settings will kick in.
1. Create a new Article node, along with the title, body some tags and an image.
At this point nothing should happen, as we haven't enabled the setting that
overrides core's default Node entity view builder.
1. Reload the article node, and see how it's theme has changed.
It will look roughly like this:

![Article node view](https://gitlab.com/drupalspoons/pluggable_entity_view_builder/uploads/a4023fdb7652a2ec039b5e69ce09d1fb/Selection_999_1093_.png)


## Paragraphs Integration

PEVB has integration with [Paragraphs](https://www.drupal.org/project/paragraphs)
module, as Paragraphs are nothing more than an entity, similar to a Node.

With Paragraphs module we may create landing pages such as the homepage, search
page, contact us, etc. that are highly customizable by site administrators.
This can be an alternative to core's Layout builder, in that it allows
developers to theme each element by itself, while site administrators can mix
and match those elements into a page.

See it in action:

1. Enable the "Pluggable Entity View Builder Paragraphs Example" module.
1. Article content type will now have a new "Paragraphs" field.
1. Create a few Paragraphs and view the node.


## Media Entities

PEVB supports Media entities, mainly to allow control of how they look when
added in content through the WYSIWYG editor.

Note that when media entities are referenced through a field in another entity
like a node that is being rendered with PEVB, you can theme the media directly
from that entity's PEVB, for fewer levels of indirections.
See `BuildFieldTrait::getMediaImageAndAlt()` for example.
