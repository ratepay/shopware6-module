import {
  __commonJS
} from "./chunk-BUSYA2B4.js";

// ../../../../../../custom/plugins/Ratepay/src/Resources/app/administration/node_modules/xml-parser-xo/index.js
var require_xml_parser_xo = __commonJS({
  "../../../../../../custom/plugins/Ratepay/src/Resources/app/administration/node_modules/xml-parser-xo/index.js"(exports, module) {
    function parse(xml, options = {}) {
      options.filter = options.filter || (() => true);
      function nextChild() {
        return tag() || content() || comment() || cdata();
      }
      function nextRootChild() {
        match(/\s*/);
        return tag(true) || comment() || doctype() || processingInstruction(false);
      }
      function document() {
        const decl = declaration();
        const children = [];
        let documentRootNode;
        let child = nextRootChild();
        while (child) {
          if (child.node.type === "Element") {
            if (documentRootNode) {
              throw new Error("Found multiple root nodes");
            }
            documentRootNode = child.node;
          }
          if (!child.excluded) {
            children.push(child.node);
          }
          child = nextRootChild();
        }
        if (!documentRootNode) {
          throw new Error("Failed to parse XML");
        }
        return {
          declaration: decl ? decl.node : null,
          root: documentRootNode,
          children
        };
      }
      function declaration() {
        return processingInstruction(true);
      }
      function processingInstruction(matchDeclaration) {
        const m = matchDeclaration ? match(/^<\?(xml)\s*/) : match(/^<\?([\w-:.]+)\s*/);
        if (!m) return;
        const node = {
          name: m[1],
          type: "ProcessingInstruction",
          attributes: {}
        };
        while (!(eos() || is("?>"))) {
          const attr = attribute();
          if (!attr) return node;
          node.attributes[attr.name] = attr.value;
        }
        match(/\?>/);
        return {
          excluded: matchDeclaration ? false : options.filter(node) === false,
          node
        };
      }
      function tag(matchRoot) {
        const m = match(/^<([\w-:.]+)\s*/);
        if (!m) return;
        const node = {
          type: "Element",
          name: m[1],
          attributes: {},
          children: []
        };
        while (!(eos() || is(">") || is("?>") || is("/>"))) {
          const attr = attribute();
          if (!attr) return node;
          node.attributes[attr.name] = attr.value;
        }
        const excluded = matchRoot ? false : options.filter(node) === false;
        if (match(/^\s*\/>/)) {
          node.children = null;
          return {
            excluded,
            node
          };
        }
        match(/\??>/);
        if (!excluded) {
          let child = nextChild();
          while (child) {
            if (!child.excluded) {
              node.children.push(child.node);
            }
            child = nextChild();
          }
        }
        match(/^<\/[\w-:.]+>/);
        return {
          excluded,
          node
        };
      }
      function doctype() {
        const m = match(/^<!DOCTYPE\s+[^>]*>/);
        if (m) {
          const node = {
            type: "DocumentType",
            content: m[0]
          };
          return {
            excluded: options.filter(node) === false,
            node
          };
        }
      }
      function cdata() {
        if (xml.startsWith("<![CDATA[")) {
          const endPositionStart = xml.indexOf("]]>");
          if (endPositionStart > -1) {
            const endPositionFinish = endPositionStart + 3;
            const node = {
              type: "CDATA",
              content: xml.substring(0, endPositionFinish)
            };
            xml = xml.slice(endPositionFinish);
            return {
              excluded: options.filter(node) === false,
              node
            };
          }
        }
      }
      function comment() {
        const m = match(/^<!--[\s\S]*?-->/);
        if (m) {
          const node = {
            type: "Comment",
            content: m[0]
          };
          return {
            excluded: options.filter(node) === false,
            node
          };
        }
      }
      function content() {
        const m = match(/^([^<]+)/);
        if (m) {
          const node = {
            type: "Text",
            content: m[1]
          };
          return {
            excluded: options.filter(node) === false,
            node
          };
        }
      }
      function attribute() {
        const m = match(/([\w-:.]+)\s*=\s*("[^"]*"|'[^']*'|\w+)\s*/);
        if (!m) return;
        return { name: m[1], value: strip(m[2]) };
      }
      function strip(val) {
        return val.replace(/^['"]|['"]$/g, "");
      }
      function match(re) {
        const m = xml.match(re);
        if (!m) return;
        xml = xml.slice(m[0].length);
        return m;
      }
      function eos() {
        return 0 === xml.length;
      }
      function is(prefix) {
        return 0 === xml.indexOf(prefix);
      }
      xml = xml.trim();
      return document();
    }
    module.exports = parse;
  }
});

// ../../../../../../custom/plugins/Ratepay/src/Resources/app/administration/node_modules/xml-formatter/index.js
var require_xml_formatter = __commonJS({
  "../../../../../../custom/plugins/Ratepay/src/Resources/app/administration/node_modules/xml-formatter/index.js"(exports, module) {
    function newLine(state) {
      if (!state.options.indentation && !state.options.lineSeparator) return;
      state.content += state.options.lineSeparator;
      let i;
      for (i = 0; i < state.level; i++) {
        state.content += state.options.indentation;
      }
    }
    function appendContent(state, content) {
      state.content += content;
    }
    function processNode(node, state, preserveSpace) {
      if (typeof node.content === "string") {
        processContentNode(node, state, preserveSpace);
      } else if (node.type === "Element") {
        processElementNode(node, state, preserveSpace);
      } else if (node.type === "ProcessingInstruction") {
        processProcessingIntruction(node, state, preserveSpace);
      } else {
        throw new Error("Unknown node type: " + node.type);
      }
    }
    function processContentNode(node, state, preserveSpace) {
      if (!preserveSpace) {
        node.content = node.content.trim();
      }
      if (node.content.length > 0) {
        if (!preserveSpace && state.content.length > 0) {
          newLine(state);
        }
        appendContent(state, node.content);
      }
    }
    function processElementNode(node, state, preserveSpace) {
      if (!preserveSpace && state.content.length > 0) {
        newLine(state);
      }
      appendContent(state, "<" + node.name);
      processAttributes(state, node.attributes);
      if (node.children === null) {
        const selfClosingNodeClosingTag = state.options.whiteSpaceAtEndOfSelfclosingTag ? " />" : "/>";
        appendContent(state, selfClosingNodeClosingTag);
      } else if (node.children.length === 0) {
        appendContent(state, "></" + node.name + ">");
      } else {
        appendContent(state, ">");
        state.level++;
        let nodePreserveSpace = node.attributes["xml:space"] === "preserve";
        if (!nodePreserveSpace && state.options.collapseContent) {
          let containsTextNodes = false;
          let containsTextNodesWithLineBreaks = false;
          let containsNonTextNodes = false;
          node.children.forEach(function(child, index) {
            if (child.type === "Text") {
              if (child.content.includes("\n")) {
                containsTextNodesWithLineBreaks = true;
                child.content = child.content.trim();
              } else if (index === 0 || index === node.children.length - 1) {
                if (child.content.trim().length === 0) {
                  child.content = "";
                }
              }
              if (child.content.length > 0) {
                containsTextNodes = true;
              }
            } else if (child.type === "CDATA") {
              containsTextNodes = true;
            } else {
              containsNonTextNodes = true;
            }
          });
          if (containsTextNodes && (!containsNonTextNodes || !containsTextNodesWithLineBreaks)) {
            nodePreserveSpace = true;
          }
        }
        node.children.forEach(function(child) {
          processNode(child, state, preserveSpace || nodePreserveSpace, state.options);
        });
        state.level--;
        if (!preserveSpace && !nodePreserveSpace) {
          newLine(state);
        }
        appendContent(state, "</" + node.name + ">");
      }
    }
    function processAttributes(state, attributes) {
      Object.keys(attributes).forEach(function(attr) {
        const escaped = attributes[attr].replace(/"/g, "&quot;");
        appendContent(state, " " + attr + '="' + escaped + '"');
      });
    }
    function processProcessingIntruction(node, state) {
      if (state.content.length > 0) {
        newLine(state);
      }
      appendContent(state, "<?" + node.name);
      processAttributes(state, node.attributes);
      appendContent(state, "?>");
    }
    function format(xml, options = {}) {
      options.indentation = "indentation" in options ? options.indentation : "    ";
      options.collapseContent = options.collapseContent === true;
      options.lineSeparator = "lineSeparator" in options ? options.lineSeparator : "\r\n";
      options.whiteSpaceAtEndOfSelfclosingTag = !!options.whiteSpaceAtEndOfSelfclosingTag;
      const parser = require_xml_parser_xo();
      const parsedXml = parser(xml, { filter: options.filter });
      const state = { content: "", level: 0, options };
      if (parsedXml.declaration) {
        processProcessingIntruction(parsedXml.declaration, state);
      }
      parsedXml.children.forEach(function(child) {
        processNode(child, state, false);
      });
      return state.content.replace(/\r\n/g, "\n").replace(/\n/g, options.lineSeparator);
    }
    module.exports = format;
  }
});
export default require_xml_formatter();
//# sourceMappingURL=xml-formatter.js.map
