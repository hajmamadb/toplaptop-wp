(function (wp, config) {
  if (!wp || !wp.blocks || !config) {
    return;
  }
  var el = wp.element.createElement;
  var useBlockProps = wp.blockEditor && wp.blockEditor.useBlockProps;

  Object.keys(config.blocks).forEach(function (name) {
    var block = config.blocks[name];
    wp.blocks.registerBlockType(name, {
      apiVersion: 2,
      title: block.title,
      description: block.description,
      category: "supportcandy",
      icon: "tickets-alt",
      supports: { html: false },
      edit: function () {
        var props = useBlockProps
          ? useBlockProps({
              style: {
                padding: "16px",
                border: "1px dashed #8c8f94",
                borderRadius: "4px",
                background: "#f6f7f7",
              },
            })
          : {};
        return el(
          "div",
          props,
          el("strong", null, "SupportCandy: " + block.title),
          el("p", { style: { margin: "4px 0" } }, block.description),
          el("code", null, "[" + block.shortcode + "]")
        );
      },
      save: function () {
        return null;
      },
    });
  });
})(window.wp, window.wpscBlocks);
