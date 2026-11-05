export function run(nova) {
  nova.writeWorkspace("nova-github-demo.txt", "nova-github-demo 0.1.0");
  nova.log("nova-github-demo 0.1.0");
  return "nova-github-demo-ok-0.1.0";
}
